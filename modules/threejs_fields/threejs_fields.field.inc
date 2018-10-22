<?php

/**
 * @file
 * Implement an threejs fields, based on the file module's file field.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for ThreeJS Fields widget templates.
 *
 * Default template: threejs-file-widget.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: A render element representing the threejs field widget.
 */
function template_preprocess_threejs_file_widget(&$variables) {
  $element = $variables['element'];

  $variables['attributes'] = ['class' => ['threejs-widget', 'threejs-file-widget', 'js-form-managed-file', 'form-managed-file', 'clearfix']];

  if (!empty($element['fids']['#value'])) {
    $file = reset($element['#files']);
    $element['file_' . $file->id()]['filename']['#suffix'] = ' <span class="file-size">(' . format_size($file->getSize()) . ')</span> ';
  }

  $variables['data'] = [];
  foreach (Element::children($element) as $child) {
    $variables['data'][$child] = $element[$child];
  }

}

/**
 * Prepares variables for ThreeJS formatter templates.
 *
 * Default template: threejs-formatter.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - item: An ImageItem object.
 *   - item_attributes: An optional associative array of html attributes to be
 *     placed in the img tag.
 *   - canvas: An optional image style.
 *   - url: An optional \Drupal\Core\Url object.
 */
function template_preprocess_threejs_formatter(&$variables) {

  if ($variables['canvas']) {
    $variables['canvas'] = [
      '#theme' => 'threejs_formatter',
    ];
  }
  else {
    $variables['canvas'] = [
      '#theme' => 'threejs_formatter',
    ];
  }
  $variables['canvas']['#attributes'] = $variables['item_attributes'];

  $item = $variables['item'];

  // Do not output an empty 'title' attribute.
  if (mb_strlen($item->title) != 0) {
    $variables['canvas']['#title'] = $item->title;
  }

  if (($entity = $item->entity) && empty($item->uri)) {
    $variables['canvas']['#uri'] = $entity->getFileUri();
  }
  else {
    $variables['canvas']['#uri'] = $item->uri;
  }

  foreach (['width', 'height', 'alt'] as $key) {
    $variables['canvas']["#$key"] = $item->$key;
  }
}
