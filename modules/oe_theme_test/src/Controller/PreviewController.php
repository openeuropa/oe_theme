<?php

namespace Drupal\oe_theme_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\oe_theme_test\ThemePreviewManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PreviewController.
 */
class PreviewController extends ControllerBase {

  /**
   * Theme preview manager.
   *
   * @var \Drupal\oe_theme_test\ThemePreviewManager
   */
  protected $previewManager;

  /**
   * Constructs a new PreviewController object.
   */
  public function __construct(ThemePreviewManager $preview_manager) {
    $this->previewManager = $preview_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.theme_preview')
    );
  }

  /**
   * Render.
   *
   * @return array
   *   Return Hello string.
   */
  public function render() {
    $render = [];
    foreach ($this->previewManager->getDefinitions() as $id => $definition) {

      $render[$id] = [
        'label' => ['#markup' => $definition['label']],
        'description' => ['#markup' => $definition['description']],
        'preview' => $definition['preview'],
      ];
    }
    return $render;
  }

}
