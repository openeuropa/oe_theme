<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\EventSubscriber;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\Event\NodeMetadataEvent;
use Drupal\oe_theme_helper\PageHeaderMetadataEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides the node entity on the default node-related routes.
 *
 * @see \Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase
 */
class DefaultNodeMetadataEventSubscriber implements EventSubscriberInterface {

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
   * Creates a new DefaultNodeMetadataEventSubscriber object.
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
   * Returns the node from the current route for common node routes.
   *
   * @param \Drupal\oe_theme_helper\Event\NodeMetadataEvent $event
   *   The event.
   */
  public function getNode(NodeMetadataEvent $event): void {
    $supported = [
      'entity.node.canonical',
      'entity.node.latest_version',
      'entity.node.revision',
    ];

    if (!in_array($this->currentRouteMatch->getRouteName(), $supported)) {
      return;
    }

    if ($this->currentRouteMatch->getRouteName() === 'entity.node.revision') {
      $node_revision = $this->currentRouteMatch->getRawParameter('node_revision');
      $node = $this->entityTypeManager->getStorage('node')->loadRevision($node_revision);
      $translated_node = $node ? $this->entityRepository->getTranslationFromContext($node) : NULL;

      $event->setNode($translated_node);
      $event->addCacheableDependency($translated_node)
        ->addCacheContexts(['route']);

      return;
    }

    // If a node object is present in the route, use that one.
    $node = $this->currentRouteMatch->getParameter('node');

    if ($node instanceof NodeInterface) {
      $event->setNode($node);
      $event->addCacheableDependency($node)
        ->addCacheContexts(['route']);
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[PageHeaderMetadataEvents::NODE][] = ['getNode'];

    return $events;
  }

}
