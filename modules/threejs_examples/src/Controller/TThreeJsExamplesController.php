<?php

namespace Drupal\threejs_examples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\example\ExampleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for tThree.js Examples routes.
 */
class TThreeJsExamplesController extends ControllerBase {

  /**
   * The example service.
   *
   * @var \Drupal\example\ExampleInterface
   */
  protected $example;

  /**
   * Constructs the controller object.
   *
   * @param \Drupal\example\ExampleInterface $example
   *   The example service.
   */
  public function __construct(ExampleInterface $example) {
    $this->example = $example;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('example')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
