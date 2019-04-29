<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\PageHeaderMetadata;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\PageHeaderMetadataPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base plugin to handle metadata for node view routes.
 *
 * This is a base plugin as it should be extended to return extra metadata
 * for the node.
 */
abstract class NodeViewRouteBase extends PageHeaderMetadataPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new NodeViewRouteBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RouteMatchInterface $current_route_match, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentRouteMatch = $current_route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $entity = $this->getNode();

    $metadata = [];

    $cacheability = new CacheableMetadata();
    $cacheability
      ->addCacheableDependency($entity)
      ->addCacheContexts(['route'])
      ->applyTo($metadata);

    return $metadata;
  }

  /**
   * Returns the node retrieved from the current route.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node entity, or NULL if not found.
   */
  protected function getNode(): ?NodeInterface {
    $supported = [
      'entity.node.canonical',
      'entity.node.latest_version',
      'entity.node.revision',
    ];

    if (!in_array($this->currentRouteMatch->getRouteName(), $supported)) {
      return NULL;
    }

    if ($this->currentRouteMatch->getRouteName() === 'entity.node.revision') {
      $node_revision = $this->currentRouteMatch->getParameter('node_revision');
      $node = $this->entityTypeManager->getStorage('node')->loadRevision($node_revision);

      return $node ? $this->entityRepository->getTranslationFromContext($node) : NULL;
    }

    // If a node object is present in the route, use that one.
    $node = $this->currentRouteMatch->getParameter('node');
    return $node instanceof NodeInterface ? $node : NULL;
  }

}
