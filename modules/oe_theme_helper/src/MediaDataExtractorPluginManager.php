<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * MediaDataExtractor plugin manager.
 *
 * Note: the service is marked as internal as it will be revisited in the next
 * releases to adapt more code to use it. Its API will most likely change.
 *
 * @internal
 */
class MediaDataExtractorPluginManager extends DefaultPluginManager implements MediaDataExtractorPluginManagerInterface {

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a MediaDataExtractorPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct(
      'Plugin/MediaDataExtractor',
      $namespaces,
      $module_handler,
      'Drupal\oe_theme_helper\MediaDataExtractorInterface',
      'Drupal\oe_theme_helper\Annotation\MediaDataExtractor'
    );
    $this->alterInfo('oe_theme_media_data_extractor_info');
    $this->setCacheBackend($cache_backend, 'oe_theme_media_data_extractor_plugins');

    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstanceByMediaBundle(string $bundle): MediaDataExtractorInterface {
    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo('media');

    if (!isset($bundle_info[$bundle]['media_data_extractor'])) {
      throw new PluginException(sprintf('Media data extractor plugin not declared for "%s" bundle.', $bundle));
    }

    return $this->createInstance($bundle_info[$bundle]['media_data_extractor']);
  }

}
