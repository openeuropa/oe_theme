<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_demo;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Provides the default demo_block manager.
 */
class DemoBlockPluginManager extends DefaultPluginManager {

  /**
   * Provides default values for plugin definitions.
   *
   * @var array
   */
  protected $defaults = [
    'id' => '',
    'label' => '',
    'theme_hook' => '',
  ];

  /**
   * Constructs a new DemoBlockPluginManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    // Add more services as required.
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'demo_block', ['demo_block']);
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    $definition['theme_hook'] = 'demo_block_' . $definition['id'];
  }

  /**
   * Return demo blocks theme implementations.
   *
   * @return array
   *   Theme implementations.
   *
   * @see oe_theme_demo_theme()
   */
  public function hookTheme() {
    $items = [];
    foreach ($this->getDefinitions() as $definition) {
      $items[$definition['theme_hook']] = ['variables' => []];
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('demo_blocks', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

}
