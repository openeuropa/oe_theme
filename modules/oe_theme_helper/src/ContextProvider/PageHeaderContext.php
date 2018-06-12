<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\ContextProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\PageHeaderMetadataPluginManager;

/**
 * Provides metadata for the current page as context.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class PageHeaderContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The page header metadata plugin mananger.
   *
   * @var \Drupal\oe_theme_helper\PageHeaderMetadataPluginManager
   */
  protected $metadataPluginManager;

  /**
   * Instanciates a new PageHeader context object.
   *
   * @param \Drupal\oe_theme_helper\PageHeaderMetadataPluginManager $metadata_plugin_manager
   *   The page header metadata plugin manager.
   */
  public function __construct(PageHeaderMetadataPluginManager $metadata_plugin_manager) {
    $this->metadataPluginManager = $metadata_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids): array {
    $metadata = NULL;

    foreach ($this->metadataPluginManager->getDefinitions() as $id => $definition) {
      $plugin = $this->metadataPluginManager->createInstance($id);

      if ($plugin->applies()) {
        $metadata = $plugin->getMetadata();
        break;
      }
    }

    $context = new Context(new ContextDefinition('map', $this->t('Metadata')), $metadata);

    if (!empty($metadata)) {
      $cacheability = CacheableMetadata::createFromRenderArray($metadata);
      $context->addCacheableDependency($cacheability);
    }

    return ['page_header' => $context];
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts(): array {
    $context = new Context(new ContextDefinition('map', $this->t('Metadata')));

    return ['page_header' => $context];
  }

}
