<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_demo\Plugin\Derivative;

use Drupal\oe_theme_demo\DemoBlockPluginManager;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DemoBlock.
 */
class DemoBlock extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Demo block manager service.
   *
   * @var \Drupal\oe_theme_demo\DemoBlockPluginManager
   */
  protected $blockManager;

  /**
   * DemoBlock constructor.
   *
   * @param \Drupal\oe_theme_demo\DemoBlockPluginManager $blockManager
   *   Demo block manager service.
   */
  public function __construct(DemoBlockPluginManager $blockManager) {
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.oe_theme_demo.demo_blocks')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.LongVariable)
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];
    foreach ($this->blockManager->getDefinitions() as $id => $definition) {
      $this->derivatives[$id] = [
        'label' => $definition['label'],
        'admin_label' => $definition['label'],
        'theme_hook' => $definition['theme_hook'],
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
