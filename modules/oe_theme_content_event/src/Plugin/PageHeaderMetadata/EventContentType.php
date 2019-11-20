<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\PageHeaderMetadata;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page header metadata for the OpenEuropa Event content entity.
 *
 * @PageHeaderMetadata(
 *   id = "event_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Event content type"),
 *   weight = -1
 * )
 */
class EventContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Creates a new EventContentType object.
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
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RouteMatchInterface $current_route_match, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, EventDispatcherInterface $event_dispatcher, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $current_route_match, $entity_type_manager, $entity_repository, $event_dispatcher);
    $this->dateFormatter = $date_formatter;
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
      $container->get('entity.repository'),
      $container->get('event_dispatcher'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_event';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node = $this->getNode();

    $event_type_field = $node->get('oe_event_type');
    $event_type_value = $event_type_field->value;
    $event_type_options = $event_type_field->getFieldDefinition()->getSetting('allowed_values');
    $metadata['metas'] = [
      $this->t('@term', ['@term' => $event_type_options[$event_type_value]]),
    ];

    return $metadata;
  }

}
