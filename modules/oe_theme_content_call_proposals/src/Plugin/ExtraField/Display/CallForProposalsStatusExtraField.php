<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_call_proposals\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\oe_content\CallEntityWrapperInterface;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapper;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display Call for proposals status.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_call_proposals_status",
 *   label = @Translation("Status"),
 *   bundles = {
 *     "node.oe_call_proposals",
 *   },
 *   visible = true
 * )
 */
class CallForProposalsStatusExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Time based cache tag generator service.
   *
   * @var \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface
   */
  protected $cacheTagGenerator;

  /**
   * CallForProposalsStatusExtraField constructor.
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
    $entity = CallForProposalsNodeWrapper::getInstance($entity);
    $cacheable = CacheableMetadata::createFromRenderArray(['#cache' => ['contexts' => ['timezone']]]);

    $status = $entity->getStatus();
    // Set cache tags based on date.
    if ($status === CallEntityWrapperInterface::STATUS_UPCOMING) {
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($entity->getOpeningDate()->getPhpDateTime()));
    }
    if ($status === CallEntityWrapperInterface::STATUS_OPEN
      && !empty($entity->getDeadlineDate())) {
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($entity->getDeadlineDate()->getPhpDateTime()));
    }
    $build = [
      '#theme' => 'oe_theme_helper_call_status',
      '#label' => $entity->getStatusLabel(),
      '#name' => 'call-proposals-status',
    ];
    $cacheable->applyTo($build);

    return $build;
  }

}
