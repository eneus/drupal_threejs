<?php

namespace Drupal\threejs_fields\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileItem;

/**
 * Plugin implementation of the 'threejs_file' field type.
 *
 * @FieldType(
 *   id = "threejs_file",
 *   label = @Translation("ThreeJS File"),
 *   description = @Translation("ThreeJS Files created by 3D Software"),
 *   category = @Translation("Reference"),
 *   default_widget = "threejs_file_widget",
 *   default_formatter = "threejs_file_formatter",
 *   column_groups = {
 *     "file" = {
 *       "label" = @Translation("File"),
 *       "columns" = {
 *         "target_id", "options"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "title" = {
 *       "label" = @Translation("Title"),
 *       "translatable" = TRUE
 *     },
 *   },
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class ThreeJSFile extends FileItem {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'default_canvas' => [
        'uuid' => NULL,
        'title' => '',
        'options' => [
          'rotation' => [
            'x' => '0.01',
            'y' => '0.1',
          ],
          'camera' => [
            'position' => [
              'x' => '-20',
              'y' => '30',
              'z' => '40',
            ],
            'controls' => [
              'orbit', 'fly',
              ],
            ],
          ],
        ],
      ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = [
        'file_extensions' => 'obj dae glb gltf stl pdb',
        'title_field' => 0,
        'title_field_required' => 0,
        'default_canvas' => [
          'uuid' => NULL,
          'title' => '',
          'options' => [
            'rotation' => [
              'x' => '0.01',
              'y' => '0.1',
            ],
            'camera' => [
              'position' => [
                'x' => '-20',
                'y' => '30',
                'z' => '40',
              ],
              'controls' => [
                'orbit', 'fly',
              ],
            ],
          ],
        ],
      ] + parent::defaultFieldSettings();

    unset($settings['description_field']);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'target_id' => [
          'description' => 'The ID of the file entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'title' => [
          'description' => "Canvas title text, for the canvas's 'title' attribute.",
          'type' => 'varchar',
          'length' => 1024,
        ],
        'options' => [
          'description' => 'A serialized configuration canvas model object data.',
          'type' => 'blob',
          'not null' => FALSE,
          'size' => 'big',
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table' => 'file_managed',
          'columns' => ['target_id' => 'fid'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    unset($properties['display']);
    unset($properties['description']);

    $properties['options'] = DataDefinition::create('any')
      ->setLabel(t('options'))
      ->setDescription(t('Options of the canvas object.'));

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t("Canvas title text, for the Canvas's 'title' attribute."));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];

    // We need the field-level 'default_canvas' setting, and $this->getSettings()
    // will only provide the instance-level one, so we need to explicitly fetch
    // the field.
    $settings = $this->getFieldDefinition()->getFieldStorageDefinition()->getSettings();

    $scheme_options = \Drupal::service('stream_wrapper_manager')->getNames(StreamWrapperInterface::WRITE_VISIBLE);
    $element['uri_scheme'] = [
      '#type' => 'radios',
      '#title' => t('Upload destination'),
      '#options' => $scheme_options,
      '#default_value' => $settings['uri_scheme'],
      '#description' => t('Select where the final files should be stored. Private file storage has significantly more overhead than public files, but allows restricted access to files within this field.'),
    ];

    // Add default_canvas element.
    static::defaultThreeJSFileForm($element, $settings);
    $element['default_canvas']['#description'] = t('This default Canvas object rendering options.');

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // Get base form from FileItem.
    $element = parent::fieldSettingsForm($form, $form_state);

    $settings = $this->getSettings();

    // Remove the description option.
    unset($element['description_field']);

    // Add title configuration options.
    $element['title_field'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable <em>Title</em> field'),
      '#default_value' => $settings['title_field'],
      '#description' => t('The title attribute is used as a tooltip when the mouse hovers over the image. Enabling this field is not recommended as it can cause problems with screen readers.'),
      '#weight' => 11,
    ];
    $element['title_field_required'] = [
      '#type' => 'checkbox',
      '#title' => t('<em>Title</em> field required'),
      '#default_value' => $settings['title_field_required'],
      '#weight' => 12,
      '#states' => [
        'visible' => [
          ':input[name="settings[title_field]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Add default_canvas element.
    static::defaultThreeJSFileForm($element, $settings);
    $element['default_canvas']['#description'] = t("This default configurations for uploaded or created 3D Canvas objects.");

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();

    $options = $this->options;

    // Determine the dimensions if necessary.
    if ($this->entity && $this->entity instanceof EntityInterface) {
//      if (empty($width) || empty($height)) {
//        $canvas = \Drupal::service('threejs.factory')->get($this->entity->getFileUri());
//        if ($canvas->isValid()) {
//          $this->options =
//          $this->width = $image->getWidth();
//          $this->height = $image->getHeight();
//        }
//      }
    }
    else {
      trigger_error(sprintf("Missing file with ID %s.", $this->target_id), E_USER_WARNING);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $settings = $field_definition->getSettings();
    static $canvases = [];

    $extensions = array_intersect(explode(' ', $settings['file_extensions']), ['obj', 'dae', 'glb', 'gltf', 'stl', 'pdb']);
    $extension = array_rand(array_combine($extensions, $extensions));
    // Generate a max of 5 different images.

    $values = [
      'target_id' => $file->id(),
      'options' => $random->sentences(4),
      'title' => $random->sentences(4),
    ];
    return $values;
  }

  /**
   * Builds the default_canvas details element.
   *
   * @param array $element
   *   The form associative array passed by reference.
   * @param array $settings
   *   The field settings array.
   */
    protected function defaultThreeJSFileForm(array &$element, array $settings) {
    $element['default_canvas'] = [
      '#type' => 'details',
      '#title' => t('Default Canvas options'),
      '#open' => TRUE,
    ];
    // Convert the stored UUID to a FID.
    $fids = [];
    $uuid = $settings['default_canvas']['uuid'];
    if ($uuid && ($file = $this->getEntityManager()->loadEntityByUuid('file', $uuid))) {
      $fids[0] = $file->id();
    }
    $element['default_canvas']['uuid'] = [
      '#type' => 'managed_file',
      '#title' => t('Canvas object'),
      '#description' => t('Canvas object to be shown if no Canvas object is uploaded.'),
      '#default_value' => $fids,
      '#upload_location' => $settings['uri_scheme'] . '://default_canvas/',
      '#element_validate' => [
        '\Drupal\file\Element\ManagedFile::validateManagedFile',
        [get_class($this), 'validateDefaultThreeJSFileForm'],
      ],
      '#upload_validators' => $this->getUploadValidators(),
    ];

    $element['default_canvas']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#description' => t('The title attribute is used as a tooltip when the mouse hovers over the image.'),
      '#default_value' => $settings['default_canvas']['title'],
      '#maxlength' => 1024,
    ];
    $element['default_canvas']['options'] = [
      '#type' => 'details',
      '#title' => $this->t('3D Canvas options'),
      '#collapsed' => FALSE,
    ];
    // Animation settings.
    $element['default_canvas']['options']['rotation'] = [
      '#type' => 'item',
      '#title' => t('Animation canvas object'),
//      '#element_validate' => [[get_class($this), 'validateResolution']],
      '#weight' => 4.1,
      '#field_prefix' => '<div class="container-inline">',
      '#field_suffix' => '</div>',
      '#description' => t('Animate Canvas object by X and Y (e.g. 0.1 and 0.001). Leave blank for no animation or using camera OrbitControls().'),
    ];
    $element['default_canvas']['options']['rotation']['x'] = [
      '#type' => 'textfield',
      '#title' => t('Rotation X'),
      '#title_display' => 'invisible',
      '#default_value' => $settings['default_canvas']['options']['rotation']['x'],
      '#min' => 0,
      '#size' => 20,
      '#maxlength' => 24,
      '#field_suffix' => ' × ',
    ];
    $element['default_canvas']['options']['rotation']['y'] = [
      '#type' => 'textfield',
      '#title' => t('Rotation Y'),
      '#title_display' => 'invisible',
      '#default_value' => $settings['default_canvas']['options']['rotation']['y'],
      '#min' => 0,
      '#size' => 20,
      '#maxlength' => 24,
      '#field_suffix' => ' ' . t('milliseconds'),
    ];

    // Camera settings
    $element['default_canvas']['options']['camera'] = [
      '#type' => 'details',
      '#title' => $this->t('Camera Options'),
      '#weight' => 4.2,
      '#open' => TRUE,
    ];
    $element['default_canvas']['options']['camera']['position'] = [
      '#type' => 'item',
      '#title' => t('Camera position settings'),
//      '#element_validate' => [[get_class($this), 'validateResolution']],
      '#field_prefix' => '<div class="container-inline">',
      '#field_suffix' => '</div>',
      '#description' => t('Position and point the camera to the center of the scene.'),
    ];
    $element['default_canvas']['options']['camera']['position']['x'] = [
      '#type' => 'textfield',
      '#title' => t('Camera position X'),
      '#title_display' => 'invisible',
      '#default_value' => $settings['default_canvas']['options']['camera']['position']['x'],
      '#min' => 0,
      '#size' => 20,
      '#maxlength' => 24,
      '#field_suffix' => ' × ',
    ];
    $element['default_canvas']['options']['camera']['position']['y'] = [
      '#type' => 'textfield',
      '#title' => t('Camera position Y'),
      '#title_display' => 'invisible',
      '#default_value' => $settings['default_canvas']['options']['camera']['position']['y'],
      '#min' => 0,
      '#size' => 20,
      '#maxlength' => 24,
      '#field_suffix' => ' × ',
    ];
    $element['default_canvas']['options']['camera']['position']['z'] = [
      '#type' => 'textfield',
      '#title' => t('Camera position Z'),
      '#title_display' => 'invisible',
      '#default_value' => $settings['default_canvas']['options']['camera']['position']['z'],
      '#min' => 0,
      '#size' => 20,
      '#maxlength' => 24,
      '#field_suffix' => ' ' . t('pixels'),
    ];

    // Camera Controls Settings
    $element['default_canvas']['options']['camera']['controls'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Camera Control options'),
      '#options' => [
        'orbit' => $this->t('Orbit'),
        'fly' => $this->t('Fly'),
        'device_orientation' => $this->t('Device Orientation'),
        'drag' => $this->t('Drag'),
        'editor' => $this->t('Editor'),
        'first_person' => $this->t('First Person'),
        'orthographic_trackball' => $this->t('Orthographic Trackball'),
        'pointer_lock' => $this->t('Pointer Lock'),
        'trackball' => $this->t('Trackball'),
        'transform' => $this->t('Transform'),
        'vr' => $this->t('VR'),
      ],
      '#default_value' => ['orbit'],
      '#description' => $this->t('Select camera Control parameters'),
    ];
  }

  /**
   * Validates the managed_file element for the default Image form.
   *
   * This function ensures the fid is a scalar value and not an array. It is
   * assigned as a #element_validate callback in
   * \Drupal\image\Plugin\Field\FieldType\ImageItem::defaultThreeJSFileForm().
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateDefaultThreeJSFileForm(array &$element, FormStateInterface $form_state) {
    // Consolidate the array value of this field to a single FID as #extended
    // for default image is not TRUE and this is a single value.
    if (isset($element['fids']['#value'][0])) {
      $value = $element['fids']['#value'][0];
      // Convert the file ID to a uuid.
      if ($file = \Drupal::entityManager()->getStorage('file')->load($value)) {
        $value = $file->uuid();
      }
    }
    else {
      $value = '';
    }
    $form_state->setValueForElement($element, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function isDisplayed() {
    // Canvas items do not have per-item visibility settings.
    return TRUE;
  }

  /**
   * Gets the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   */
  protected function getEntityManager() {
    if (!isset($this->entityManager)) {
      $this->entityManager = \Drupal::entityManager();
    }
    return $this->entityManager;
  }

}