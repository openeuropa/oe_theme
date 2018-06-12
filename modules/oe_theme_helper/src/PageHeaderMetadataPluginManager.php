<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\oe_theme_helper\Annotation\PageHeaderMetadata;

/**
 * Plugin manager for page header metadata plugins.
 */
class PageHeaderMetadataPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new page header metadata plugin manager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/PageHeaderMetadata', $namespaces, $module_handler, PageHeaderMetadataPluginInterface::class, PageHeaderMetadata::class);

    $this->alterInfo('page_header_metadata_info');
    $this->setCacheBackend($cache_backend, 'page_header_metadata_plugins');
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = parent::findDefinitions();
    uasort($definitions, [SortArray::class, 'sortByWeightElement']);

    return $definitions;
  }

}
