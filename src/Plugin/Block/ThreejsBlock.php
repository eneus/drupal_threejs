<?php

namespace Drupal\threejs\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Example' block.
 *
 * @Block(
 *   id = "threejs_block",
 *   admin_label = @Translation("Three.js"),
 *   category = @Translation("Three.js")
 * )
 */
class ThreejsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
