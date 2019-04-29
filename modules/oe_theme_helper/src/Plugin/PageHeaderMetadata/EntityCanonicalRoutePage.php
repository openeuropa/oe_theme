<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\PageHeaderMetadata;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\oe_theme_helper\PageHeaderMetadataPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a page header metadata plugin that extracts data from current entity.
 *
 * @PageHeaderMetadata(
 *   id = "entity_canonical_route",
 *   label = @Translation("Default entity metadata extractor")
 * )
 */
class EntityCanonicalRoutePage extends PageHeaderMetadataPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Creates a new EntityPageHeaderMetadata object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RouteMatchInterface $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $entity = $this->getEntityFromCurrentRoute();

    return !empty($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $entity = $this->getEntityFromCurrentRoute();

    $metadata = [
      'title' => $entity->label(),
    ];

    $cacheability = new CacheableMetadata();
    $cacheability
      ->addCacheableDependency($entity)
      ->addCacheContexts(['route'])
      ->applyTo($metadata);

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromCurrentRoute(): ?ContentEntityInterface {
    if (($route = $this->currentRouteMatch->getRouteObject()) && ($parameters = $route->getOption('parameters'))) {
      // Determine if the current route represents an entity.
      foreach ($parameters as $name => $options) {
        if (isset($options['type']) && strpos($options['type'], 'entity:') === 0) {
          $entity = $this->currentRouteMatch->getParameter($name);
          if ($entity instanceof ContentEntityInterface) {
            if ($this->currentRouteMatch->getRouteName() === "entity.{$entity->getEntityTypeId()}.canonical") {
              return $entity;
            }
            elseif ($this->currentRouteMatch->getRouteName() === "entity.{$entity->getEntityTypeId()}.revision") {
              return $entity;
            }
          }
        }
      }
    }

    return NULL;
  }

}
