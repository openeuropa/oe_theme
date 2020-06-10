<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\oe_content_entity_venue\Entity\VenueInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying event details as a list of icons and text.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_details",
 *   label = @Translation("Details"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class DetailsExtraField extends EventExtraFieldBase implements ContainerFactoryPluginInterface {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * EventExtraFieldBase constructor.
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
    return $this->t('Details');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // The pattern will take care of not displaying empty items.
    $build = [
      '#type' => 'pattern',
      '#id' => 'icons_with_text',
      '#fields' => [
        'items' => [
          [
            'icon' => 'file',
            'text' => $this->getRenderableSubject($entity),
          ],
          [
            'icon' => 'calendar',
            'text' => $this->getRenderableDates($entity),
          ],
        ],
      ],
    ];

    $this->addRenderableLocation($build, $entity);
    if (!$entity->get('oe_event_online_type')->isEmpty()) {
      $build['#fields']['items'][] = [
        'icon' => 'livestreaming',
        'text' => $this->t('Live streaming available'),
      ];
    }

    return $build;
  }

  /**
   * Get the event subject as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableSubject(ContentEntityInterface $entity): array {
    return $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get('oe_subject'), [
      'label' => 'hidden',
      'settings' => [
        'link' => FALSE,
      ],
    ]);
  }

  /**
   * Get the event dates as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableDates(ContentEntityInterface $entity): array {
    return $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get('oe_event_dates'), [
      'label' => 'hidden',
      'type' => 'daterange_default',
      'settings' => [
        'format_type' => 'oe_event_date_hour',
        'separator' => $this->t('to'),
      ],
    ]);
  }

  /**
   * Add event location to event details, if any.
   *
   * @param array $build
   *   Render array.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   */
  protected function addRenderableLocation(array &$build, ContentEntityInterface $entity): void {
    if ($entity->get('oe_event_venue')->isEmpty()) {
      return;
    }

    $venue = $entity->get('oe_event_venue')->entity;
    if (!$venue instanceof VenueInterface) {
      return;
    }

    $venue = $this->entityRepository->getTranslationFromContext($venue);
    $cacheability = CacheableMetadata::createFromRenderArray($build);

    $access = $venue->access('view', NULL, TRUE);
    $cacheability->addCacheableDependency($access);

    if (!$access->isAllowed()) {
      return;
    }

    $cacheability->addCacheableDependency($venue);
    $cacheability->applyTo($build);

    // If address is empty only return cache metadata, so it can bubble up.
    if ($venue->get('oe_address')->isEmpty()) {
      return;
    }

    // Only display locality and country, inline.
    $renderable = $this->entityTypeManager->getViewBuilder('oe_venue')->viewField($venue->get('oe_address'));

    $build['#fields']['items'][] = [
      'icon' => 'location',
      'text' => $renderable[0]['locality']['#value'] . ', ' . $renderable[0]['country']['#value'],
    ];
  }

}
