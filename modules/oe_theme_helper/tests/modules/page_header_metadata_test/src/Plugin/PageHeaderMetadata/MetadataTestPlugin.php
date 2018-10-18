<?php

declare(strict_types = 1);

namespace Drupal\page_header_metadata_test\Plugin\PageHeaderMetadata;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\oe_theme_helper\PageHeaderMetadataPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test implementation for a page header metadata plugin.
 *
 * @PageHeaderMetadata(
 *   id = "page_header_test_plugin",
 *   label = @Translation("Page header metadata test plugin"),
 *   weight = -10
 * )
 */
class MetadataTestPlugin extends PageHeaderMetadataPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The state key value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Creates a new MetadataTest object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    return $this->state->get('page_header_test_plugin_applies', FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    return $this->state->get('page_header_test_plugin_metadata', []);
  }

}
