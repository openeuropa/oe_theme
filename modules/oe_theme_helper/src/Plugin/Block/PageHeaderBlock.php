<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Page header' block.
 *
 * @Block(
 *   id = "oe_theme_helper_page_header",
 *   admin_label = @Translation("Page header"),
 *   category = @Translation("OpenEuropa"),
 *   context = {
 *     "page_header" = @ContextDefinition("map", label = @Translation("Page header metadata"))
 *   }
 * )
 */
class PageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface, TitleBlockPluginInterface, ContextAwarePluginInterface {

  use StringTranslationTrait;

  /**
   * The breadcrumb builder.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
   */
  protected $breadcrumbBuilder;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The page title: a string (plain title) or a render array (formatted title).
   *
   * @var string|array
   */
  protected $title = '';

  /**
   * Constructs a new PageHeaderBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface $breadcrumb_builder
   *   The breadcrumb builder service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, BreadcrumbBuilderInterface $breadcrumb_builder, RouteMatchInterface $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->breadcrumbBuilder = $breadcrumb_builder;
    $this->configFactory = $config_factory;
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
      $container->get('config.factory'),
      $container->get('breadcrumb'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $metadata = $this->getContext('page_header')->getContextData()->getValue();
    $title = $metadata['title'] ?? $this->title;
    $build = [
      '#type' => 'pattern',
      '#id' => 'page_header',
      '#identity' => $metadata['identity'] ?? $this->configFactory->get('system.site')->get('name'),
      '#title' => $title,
      '#introduction' => $metadata['introduction'] ?? '',
      '#metas' => $metadata['metas'] ?? [],
    ];
    return $this->addBreadcrumbSegments($build, $title);
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title): self {
    $this->title = $title;

    return $this;
  }

  /**
   * Constructs a new PageHeaderBlock instance.
   *
   * @param array $build
   *   A render array.
   * @param string $title
   *   Title of the page.
   *
   * @return array
   *   The processed render array.
   */
  protected function addBreadcrumbSegments(array $build, $title = ''): array {
    $breadcrumb = $this->breadcrumbBuilder->build($this->currentRouteMatch);
    // Add segments to the breadcrumb key.
    /** @var \Drupal\Core\Link $link */
    foreach ($breadcrumb->getLinks() as $link) {
      $build['#breadcrumb'][] = [
        'href' => $link->getUrl(),
        'label' => $link->getText(),
      ];
    }
    // Add the title to the segments only if it's not empty.
    if (!empty($title)) {
      $build['#breadcrumb'][] = [
        'label' => $title,
      ];
    }
    // Make sure that the cache metadata from the breadcrumb is not lost.
    CacheableMetadata::createFromObject($breadcrumb)->applyTo($build);
    return $build;
  }

}
