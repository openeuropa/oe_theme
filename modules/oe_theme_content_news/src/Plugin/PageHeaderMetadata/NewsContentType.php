<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_news\Plugin\PageHeaderMetadata;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
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
    if (!($node->get('oe_summary')->isEmpty())) {

      $summary = $node->get('oe_summary')->first();

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
    }

    $cacheability = new CacheableMetadata();

    $news_types_labels = [];
    $news_types = $node->get('oe_news_types')->getValue();
    foreach ($news_types as $key => $news_type) {
      $news_type = $this->entityTypeManager->getStorage('skos_concept')->load($news_type['target_id']);
      $cacheability->addCacheableDependency($news_type);
      $news_type = $this->entityRepository->getTranslationFromContext($news_type);
      $news_types_labels[$key] = $news_type->label();
    }

    $timestamp = $node->get('oe_publication_date')->date->getTimestamp();

    $location = $node->get('oe_news_location')->entity;
    $cacheability->addCacheableDependency($location);

    $authors = $node->get('oe_author')->getValue();
    foreach ($authors as $key => $author) {
      $author = $this->entityTypeManager->getStorage('skos_concept')->load($author['target_id']);
      $cacheability->addCacheableDependency($author);
      $author = $this->entityRepository->getTranslationFromContext($author);
      $authors_labels[$key] = $author->label();
    }
    $metadata['metas'] = [
      isset($news_types_labels) ? $news_types_labels : $this->t('News'),
      $this->dateFormatter->format($timestamp, 'oe_theme_news_date'),
      isset($location) ? $location->label() : '',
      isset($authors_labels) ? $authors_labels : '',
    ];

    return $metadata;
  }

}
