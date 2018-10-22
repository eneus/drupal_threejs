<?php

namespace Drupal\threejs_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Base class for image file formatters.
 */
abstract class ThreeJSFormatterBase extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    // Add the default image if needed.
    if ($items->isEmpty()) {
      $default_canvas = $this->getFieldSetting('default_canvas');
      // If we are dealing with a configurable field, look in both
      // instance-level and field-level settings.
      if (empty($default_canvas['uuid']) && $this->fieldDefinition instanceof FieldConfigInterface) {
        $default_canvas = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('default_image');
      }
      if (!empty($default_canvas['uuid']) && $file = \Drupal::entityManager()->loadEntityByUuid('file', $default_canvas['uuid'])) {
        // Clone the FieldItemList into a runtime-only object for the formatter,
        // so that the fallback image can be rendered without affecting the
        // field values in the entity being rendered.
        $items = clone $items;
        $items->setValue([
          'target_id' => $file->id(),
          'title' => $default_canvas['title'],
          'width' => $default_canvas['width'],
          'height' => $default_canvas['height'],
          'entity' => $file,
          '_loaded' => TRUE,
          '_is_default' => TRUE,
        ]);
        $file->_referringItem = $items[0];
      }
    }

    return parent::getEntitiesToView($items, $langcode);
  }

}
