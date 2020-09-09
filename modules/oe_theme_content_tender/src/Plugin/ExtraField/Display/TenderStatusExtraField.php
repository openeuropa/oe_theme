<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_tender\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
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
   * Current request time, as a timestamp.
   *
   * @var int
   */
  protected $requestTime;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   * @param \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
   *   Time based cache tag generator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time, TimeBasedCacheTagGeneratorInterface $cache_tag_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestTime = $time->getRequestTime();
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
      $container->get('entity_type.manager'),
      $container->get('datetime.time'),
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
    $cacheable = CacheableMetadata::createFromRenderArray(['#cache' => ['contexts' => ['timezone']]]);

    $now = DrupalDateTime::createFromTimestamp($this->requestTime);
    $opening_date = FALSE;
    /** @var \Drupal\Core\Datetime\DrupalDateTime $closing_date */
    $closing_date = $entity->get('oe_tender_deadline')->date;

    // Get opening date.
    if (!$entity->get('oe_tender_opening_date')->isEmpty()) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $opening_date */
      $opening_date = $entity->get('oe_tender_opening_date')->date;
      // Prevent upcoming status when now & opening dates are the same.
      $opening_date->setTime(0, 0, 0);
    }

    if (empty($opening_date)) {
      $status = $this->t('N/A');
    }
    elseif ($now < $opening_date) {
      $status = $this->t('upcoming');
      // We invalidate the status when the opening date is reached.
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($opening_date->getPhpDateTime()));
    }
    elseif ($opening_date < $now && $now < $closing_date) {
      $status = $this->t('open');
      // We invalidate the status when the closing date is reached.
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($closing_date->getPhpDateTime()));
    }
    elseif ($now > $closing_date) {
      $status = $this->t('closed');
    }
    $build['#markup'] = $status;
    $cacheable->applyTo($build);

    return $build;
  }

}
