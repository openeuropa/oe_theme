<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Render\RendererInterface;
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
 *   context_definitions = {
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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, BreadcrumbBuilderInterface $breadcrumb_builder, RouteMatchInterface $current_route_match, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->breadcrumbBuilder = $breadcrumb_builder;
    $this->configFactory = $config_factory;
    $this->currentRouteMatch = $current_route_match;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('breadcrumb'),
      $container->get('current_route_match'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $metadata = $this->getContext('page_header')->getContextData()->getValue();
    $title = $metadata['title'] ?? $this->title;
    $introduction = $metadata['introduction'] ?? '';
    $slots = [
      'title' => [
        '#markup' => is_array($title) ? $this->renderer->render($title) : $title,
      ],
    ];
    if (!empty($introduction)) {
      $slots['introduction'] = is_array($introduction) ? [
        '#markup' => $this->renderer->render($introduction),
      ] : [
        '#plain_text' => $introduction,
      ];
    }
    $build = [
      '#type' => 'component',
      '#component' => 'oe_theme:page_header',
      '#slots' => $slots,
      '#props' => [
        'metas' => $metadata['metas'] ?? [],
        'background_image_url' => $metadata['background_image_url'] ?? '',
        'hide_title' => $metadata['hide_title'] ?? FALSE,
        'header_message' => $metadata['header_message'] ?? [],
      ],
    ];

    return $this->addBreadcrumbSegments($build, $title);
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
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
    // Add segments to the breadcrumb prop.
    /** @var \Drupal\Core\Link $link */
    foreach ($breadcrumb->getLinks() as $link) {
      $build['#props']['breadcrumb'][] = [
        'href' => $link->getUrl(),
        'label' => $link->getText(),
      ];
    }
    // Add the title to the segments only if it's not empty.
    if (!empty($title)) {
      $build['#props']['breadcrumb'][] = [
        'label' => $title,
      ];
    }
    // Make sure that the cache metadata from the breadcrumb is not lost.
    CacheableMetadata::createFromObject($breadcrumb)->applyTo($build);
    return $build;
  }

}
