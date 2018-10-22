<?php

namespace Drupal\threejs_fields\Plugin\Field\FieldFormatter;


use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;


/**
 * Plugin implementation of the 'threejs_file_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "threejs_file_formatter",
 *   label = @Translation("WebGL Render"),
 *   field_types = {
 *     "threejs_file"
 *   }
 * )
 */
class ThreeJSFileFormatter  extends ThreeJSFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The image style entity storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected $canvasStorage;

  /**
   * Constructs an ImageFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style storage.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, EntityStorageInterface $image_style_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->currentUser = $current_user;
    $this->imageStyleStorage = $image_style_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity.manager')->getStorage('image_style')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'canvas_background_transparent' => 0,
        'canvas_background' => '#000000',
        'canvas_width' => '100%',
        'canvas_height' => '480px',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $element['canvas_background_transparent'] = [
      '#type' => 'checkbox',
      '#title' => 'Background color scene is transparent',
      '#default_value' => $this->getSetting('canvas_background_transparent'),
    ];

    $element['canvas_background'] = [
      '#title' => t('Background color scene'),
      '#type' => 'color',
      '#default_value' => $this->getSetting('canvas_background'),
      '#empty_option' => '#000000',
    ];

    $element['canvas_width'] = [
      '#title' => t('Container scene width'),
      '#type' => 'textfield',
      '#size' => 30,
      '#maxlength' => 64,
      '#default_value' => $this->getSetting('canvas_width'),
      '#empty_option' => '100%',
    ];
    $element['canvas_height'] = [
      '#title' => t('Container scene height'),
      '#type' => 'textfield',
      '#size' => 30,
      '#maxlength' => 64,
      '#default_value' => $this->getSetting('canvas_height'),
      '#empty_option' => '640px',
    ];
    $link_types = [
      'content' => t('Content'),
      'file' => t('File'),
    ];
    $element['canvas_link'] = [
      '#title' => t('Link image to'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('canvas_link'),
      '#empty_option' => t('Nothing'),
      '#options' => $link_types,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    $canvas_background = $this->getSetting('canvas_background');
    if (isset($canvas_background)) {
      $summary[] = t('Background color scene: @color', ['@color' => $canvas_background]);
    }
    else {
      $summary[] = t('Background color scene is transparent');
    }

    $canvas_width = $this->getSetting('canvas_width');
    $canvas_height = $this->getSetting('canvas_height');
    if (isset($canvas_width) || isset($canvas_width) ) {
      $summary[] = t('Canvas scene size: @width x @height', ['@width' => isset($canvas_width) ? $canvas_width : '100%', '@height' => isset($canvas_height) ? $canvas_height : '100%']);
    }

    $link_types = [
      'content' => t('Linked to content'),
      'file' => t('Linked to file'),
    ];
    // Display this setting only if image is linked.
    $canvas_link_setting = $this->getSetting('canvas_link');
    if (isset($link_types[$canvas_link_setting])) {
      $summary[] = $link_types[$canvas_link_setting];
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    $url = NULL;
    $canvas_link_setting = $this->getSetting('canvas_link');
    // Check if the formatter involves a link.
    if ($canvas_link_setting == 'content') {
      $entity = $items->getEntity();
      if (!$entity->isNew()) {
        $url = $entity->urlInfo();
      }
    }
    elseif ($canvas_link_setting == 'file') {
      $link_file = TRUE;
    }

    // Collect cache tags to be added for each item in the field.
    $base_cache_tags = [];

    foreach ($files as $delta => $file) {
      $cache_contexts = [];

      $model_uri = $file->getFileUri();
      // @todo Wrap in file_url_transform_relative(). This is currently
      // impossible. As a work-around, we currently add the 'url.site' cache
      // context to ensure different file URLs are generated for different
      // sites in a multisite setup, including HTTP and HTTPS versions of the
      // same site. Fix in https://www.drupal.org/node/2646744.
      $url = Url::fromUri(file_create_url($model_uri));
      $cache_contexts[] = 'url.site';

      $cache_tags = Cache::mergeTags($base_cache_tags, $file->getCacheTags());

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      $item = $file->_referringItem;
      $item_attributes = $item->_attributes;
      unset($item->_attributes);

      $elements[$delta] = [
        '#theme' => 'threejs_formatter',
        '#item' => $item,
        '#item_attributes' => $item_attributes,
        '#url' => $url,
        '#cache' => [
          'tags' => $cache_tags,
          'contexts' => $cache_contexts,
        ],
        '#attached' => [
          'library' => [
            'threejs/threejs.loaders.ColladaLoader',
            'threejs/threejs.orbit.controls',
            'threejs/threejs.detector',
            'threejs/threejs.stats',
            'threejs/threejs.init'
          ],
        ],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = parent::onDependencyRemoval($dependencies);
    return $changed;
  }

//  /**
//   * Generate the output appropriate for one field item.
//   *
//   * @param \Drupal\Core\Field\FieldItemInterface $item
//   *   One field item.
//   *
//   * @return string
//   *   The textual output generated.
//   */
//  protected function viewValue(FieldItemInterface $item) {
//    // The text value has no text format assigned to it, so the user input
//    // should equal the output, including newlines.
//    return nl2br(Html::escape($item->value));
//  }

}
