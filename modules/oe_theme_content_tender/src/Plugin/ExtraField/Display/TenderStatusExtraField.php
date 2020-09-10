<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_tender\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use Drupal\oe_content_tender\TenderNodeWrapper;
use Drupal\oe_content_tender\TenderNodeWrapperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display Call for tenders status.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_tender_status",
 *   label = @Translation("Call for tenders status"),
 *   bundles = {
 *     "node.oe_tender",
 *   },
 *   visible = true
 * )
 */
class TenderStatusExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Time based cache tag generator service.
   *
   * @var \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface
   */
  protected $cacheTagGenerator;

  /**
   * TenderStatusExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
   *   Time based cache tag generator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeBasedCacheTagGeneratorInterface $cache_tag_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cacheTagGenerator = $cache_tag_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('oe_time_caching.time_based_cache_tag_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Status');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $entity = TenderNodeWrapper::getInstance($entity);
    $cacheable = CacheableMetadata::createFromRenderArray(['#cache' => ['contexts' => ['timezone']]]);

    $status = $entity->getTenderStatus();
    // Set cache tags based on date.
    if ($status === TenderNodeWrapperInterface::TENDER_STATUS_UPCOMING) {
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($entity->getOpeningDate()->getPhpDateTime()));
    }
    if ($status === TenderNodeWrapperInterface::TENDER_STATUS_OPEN) {
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($entity->getDeadlineDate()->getPhpDateTime()));
    }
    $build['#markup'] = $entity->getTenderStatusLabel();
    $cacheable->applyTo($build);

    return $build;
  }

}
