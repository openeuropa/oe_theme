<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\PageHeaderMetadata;

use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\oe_theme_helper\PageHeaderMetadataPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the default page header metadata plugin.
 *
 * @PageHeaderMetadata(
 *   id = "default",
 *   label = @Translation("Default metadata extractor")
 * )
 */
class DefaultPageHeaderMetadata extends PageHeaderMetadataPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The title resolver service.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Creates a new DefaultPageHeaderMetadata object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver service.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RequestStack $request_stack, RouteMatchInterface $current_route_match, TitleResolverInterface $title_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->requestStack = $request_stack;
    $this->currentRouteMatch = $current_route_match;
    $this->titleResolver = $title_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('title_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): ?array {
    $request = $this->requestStack->getCurrentRequest();
    $route = $this->currentRouteMatch->getRouteObject();

    $metadata = [
      'title' => $this->titleResolver->getTitle($request, $route),
    ];

    return $metadata;
  }

}
