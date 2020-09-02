<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying event details on teasers.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_teaser_details",
 *   label = @Translation("Teaser details"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = false
 * )
 */
class TeaserDetailsExtraField extends EventExtraFieldBase {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * TeaserDetailsExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->entityRepository = $entity_repository;
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
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Teaser details');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // We get here renderable arrays for contact entities.
    // We also divide them by bundle, so we can display them in a grid layout.
    $build = [
      '#theme' => 'oe_theme_content_event_teaser_details',
      '#items' => [],
    ];

    $cache = CacheableMetadata::createFromRenderArray($build);
    if (!$entity->get('oe_event_venue')->isEmpty()) {
      /** @var \Drupal\oe_content_entity_venue\Entity\VenueInterface $venue */
      $venue = $this->entityRepository->getTranslationFromContext($entity->get('oe_event_venue')->entity);
      $venue_access = $venue->access('view', NULL, TRUE);

      $cache->addCacheableDependency($venue);
      $cache->addCacheableDependency($venue_access);
      if ($venue_access->isAllowed() && !$venue->get('oe_address')->isEmpty()) {
        $renderable = $this->entityTypeManager->getViewBuilder('oe_venue')->viewField($venue->get('oe_address'));
        $build['#items'][] = [
          'icon' => 'location',
          'text' => $renderable[0]['locality']['#value'] . ', ' . $renderable[0]['country']['#value'],
        ];
      }
    }

    if (!$entity->get('oe_event_online_type')->isEmpty()) {
      $build['#items'][] = [
        'icon' => 'livestreaming',
        'text' => t('Live streaming available'),
      ];
    }

    $cache->applyTo($build);
    return $build;
  }

}
