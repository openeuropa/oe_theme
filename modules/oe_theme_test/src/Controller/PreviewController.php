<?php

namespace Drupal\oe_theme_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;

/**
 * Class PreviewController.
 */
class PreviewController extends ControllerBase {

  /**
   * Drupal\Core\Render\Renderer definition.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new PreviewController object.
   */
  public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Render.
   *
   * @return string
   *   Return Hello string.
   */
  public function render() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: render'),
    ];
  }

}
