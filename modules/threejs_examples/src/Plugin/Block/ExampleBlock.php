<?php

namespace Drupal\threejs_examples\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Example' block.
 *
 * @Block(
 *   id = "threejs_examples_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("tThree.js Examples")
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
