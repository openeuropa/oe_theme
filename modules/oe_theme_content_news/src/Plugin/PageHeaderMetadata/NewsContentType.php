<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_news\Plugin\PageHeaderMetadata;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRouteBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Page header metadata for the OpenEuropa News content entity.
 *
 * @PageHeaderMetadata(
 *   id = "news_content_type",
 *   label = @Translation("Metadata extractor for the OE Content News content type"),
 *   weight = -1
 * )
 */
class NewsContentType extends NodeViewRouteBase {

  use StringTranslationTrait;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Creates a new NewsContentType object.
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
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RouteMatchInterface $current_route_match, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $current_route_match, $entity_type_manager, $entity_repository);
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
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_news';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node = $this->getNode();
    if ($node->get('oe_summary')->isEmpty()) {
      return $metadata;
    }

    $summary = $node->get('oe_news_summary')->first();

    $metadata['introduction'] = [
      // We strip the tags because the component expects only one paragraph of
      // text and the field is using a text format which adds paragraph tags.
      '#type' => 'inline_template',
      '#template' => '{{ summary|render|striptags("<strong><a><em>")|raw }}',
      '#context' => [
        'summary' => [
          '#type' => 'processed_text',
          '#text' => $summary->value,
          '#format' => $summary->format,
          '#langcode' => $summary->getLangcode(),
        ],
      ],
    ];

    $timestamp = $node->get('oe_publication_date')->date->getTimestamp();
    $metadata['metas'] = [
      $this->t('News'),
      $this->dateFormatter->format($timestamp, 'oe_theme_news_date'),
    ];

    return $metadata;
  }

}
