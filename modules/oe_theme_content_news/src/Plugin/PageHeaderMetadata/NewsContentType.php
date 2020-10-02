<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_news\Plugin\PageHeaderMetadata;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Drupal\rdf_skos\Plugin\Field\SkosConceptReferenceFieldItemList;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page header metadata for the OpenEuropa News content entity.
 *
 * @PageHeaderMetadata(
 *   id = "news_content_type",
 *   label = @Translation("Metadata extractor for the OE Content News content type"),
 *   weight = -1
 * )
 */
class NewsContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, DateFormatterInterface $date_formatter, EntityRepositoryInterface $entity_repository, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);
    $this->dateFormatter = $date_formatter;
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('date.formatter'),
      $container->get('entity.repository'),
      $container->get('entity_type.manager')
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
    $metadata['metas'] = [];

    // Add news types to page metadata.
    if (!$node->get('oe_news_types')->isEmpty()) {
      $metadata['metas'][] = $this->getCommaSeparatedSkosMeta($node->get('oe_news_types'));
    }

    // Add publication date to page metadata.
    $timestamp = $node->get('oe_publication_date')->date->getTimestamp();
    $metadata['metas'][] = $this->dateFormatter->format($timestamp, 'oe_theme_news_date');

    // Add news locations to page metadata.
    if (!$node->get('oe_news_location')->isEmpty()) {
      $metadata['metas'][] = $this->getCommaSeparatedSkosMeta($node->get('oe_news_location'));
    }

    // Add news authors to page metadata.
    if (!$node->get('oe_author')->isEmpty()) {
      $metadata['metas'][] = $this->getCommaSeparatedSkosMeta($node->get('oe_author'));
    }

    return $metadata;
  }

  /**
   * Format a list of SKOS references into a comma separated string.
   *
   * @param \Drupal\rdf_skos\Plugin\Field\SkosConceptReferenceFieldItemList $items
   *   Field item list object.
   *
   * @return string
   *   Comma separated string.
   */
  protected function getCommaSeparatedSkosMeta(SkosConceptReferenceFieldItemList $items): string {
    $list = [];
    foreach ($items as $item) {
      $entity = $item->entity;
      $list[] = $this->entityRepository->getTranslationFromContext($entity)->label();
    }
    return implode(', ', $list);
  }

}
