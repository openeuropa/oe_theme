<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
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
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $metadata = $this->getContext('page_header')->getContextData()->getValue();

    $build = [
      '#type' => 'pattern',
      '#id' => 'page_header',
      '#identity' => $this->configFactory->get('system.site')->get('name'),
      '#title' => $metadata['title'] ?? $this->title,
      '#introduction' => $metadata['introduction'] ?? '',
      '#metas' => $metadata['metas'] ?? [],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title): self {
    $this->title = $title;

    return $this;
  }

}
