<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\EventSubscribers;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\Event\MetadataSourceRetrieveEventInterface;
use Drupal\oe_theme_helper\MetadataEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the event fired on try to retrieve information for metadata.
 */
class MetadataEventSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Creates a new MetadataEventSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(RouteMatchInterface $current_route_match, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    $this->currentRouteMatch = $current_route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Metadata source retrieving event handler.
   *
   * @param \Drupal\oe_theme_helper\Event\MetadataSourceRetrieveEventInterface $event
   *   Metadata source retrieving event.
   */
  public function onMetadataSourceRetrieving(MetadataSourceRetrieveEventInterface $event): void {
    $supported = [
      'entity.node.canonical',
      'entity.node.latest_version',
      'entity.node.revision',
    ];

    if (!in_array($this->currentRouteMatch->getRouteName(), $supported)) {
      return;
    }

    if ($this->currentRouteMatch->getRouteName() === 'entity.node.revision') {
      $node_revision = $this->currentRouteMatch->getParameter('node_revision');
      $node = $this->entityTypeManager->getStorage('node')->loadRevision($node_revision);
      $translated_node = $node ? $this->entityRepository->getTranslationFromContext($node) : NULL;

      $event->setNodeMetadataSource($translated_node);
      $event->addCacheableDependency($translated_node)
        ->addCacheContexts(['route']);

      return;
    }

    // If a node object is present in the route, use that one.
    $node = $this->currentRouteMatch->getParameter('node');

    if ($node instanceof NodeInterface) {
      $event->setNodeMetadataSource($node);
      $event->addCacheableDependency($node)
        ->addCacheContexts(['route']);
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Metadata event.
    $events[MetadataEvents::COLLECT_ENTITY][] = ['onMetadataSourceRetrieving'];

    return $events;
  }

}
