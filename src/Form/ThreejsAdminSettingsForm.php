<?php

namespace Drupal\threejs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Three.js settings for this site.
 */
class ThreejsAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'threejs_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['threejs.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $container_width = $this->config('threejs.settings')->get('container_width');
    $container_height = $this->config('threejs.settings')->get('container_height');

    $form['threejs_test'] = [
      '#type' => 'item',
      '#title' => $this->t('Test Three.js library'),
      '#markup' => '<div id="testThreejs" width="'. $container_width .'" height="'. $container_height.'"></div>',
      '#attached' => [
        'library' => [
          'threejs/threejs.orbit.controls',
          'threejs/threejs.test'
        ],
        'drupalSettings' => [
          'threejs' => [
            'container' => [
              'background_color' => $this->config('threejs.settings')->get('background_color'),
              'width' => $container_width,
              'height' => $container_height
            ]
          ]
        ]
      ],
    ];

    // Color.
    $form['background_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Default background color'),
      '#default_value' =>  $this->config('threejs.settings')->get('background_color'),
      '#description' => 'Default background color rendering in container',
    ];
    $form['container_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Conteiner width'),
      '#default_value' => $this->config('threejs.settings')->get('container_width'),
      '#description' => 'Default container rendering width. You can use percents (%), pixels(px). Example: 100%',
    ];
    $form['container_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Conteiner height'),
      '#default_value' => $this->config('threejs.settings')->get('container_height'),
      '#description' => 'Default container rendering height. You can use percents (%), pixels(px). Example: 480px',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
//    if ($form_state->getValue('background_color') != 'background_color') {
//      $form_state->setErrorByName('background_color', $this->t('The value is not correct.'));
//    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('threejs.settings')
      ->set('background_color', $form_state->getValue('background_color'))
      ->set('container_width', $form_state->getValue('container_width'))
      ->set('container_height', $form_state->getValue('container_height'))
      ->save();

    // Invalidate the library discovery cache to update new assets.
    \Drupal::service('library.discovery')->clearCachedDefinitions();

    parent::submitForm($form, $form_state);
  }

}
