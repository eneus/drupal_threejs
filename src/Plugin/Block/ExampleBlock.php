<?php

namespace Drupal\threejs\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Example' block.
 *
 * @Block(
 *   id = "threejs_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("Three.js")
 * )
 */
class ExampleBlock extends BlockBase {

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
