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

    $form['threejs_test'] = [
      '#type' => 'item',
      '#title' => $this->t('Test Three.js library'),
      '#markup' => '<div id="testThreejs"></div>',
      '#attached' => [
        'library' => [ 'threejs/threejs.init' ],
      ],
    ];
    $form['threejs'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test Setting Three.js'),
      '#default_value' => $this->config('threejs.settings')->get('testsettings'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('testsettings') != 'testsettings') {
      $form_state->setErrorByName('testsettings', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('threejs.settings')
      ->set('testsettings', $form_state->getValue('testsettings'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
