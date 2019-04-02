<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_news\Plugin\PageHeaderMetadata;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\EntityCanonicalRoutePage;
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
class NewsContentType extends EntityCanonicalRoutePage {

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
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RouteMatchInterface $current_route_match, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $current_route_match);
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
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntityFromCurrentRoute();

    return $entity instanceof NodeInterface && $entity->bundle() === 'oe_news';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $metadata['identity'] = '';

    $entity = $this->getEntityFromCurrentRoute();
    if (!$entity->get('oe_news_summary')->isEmpty()) {
      $metadata['introduction'] = [
        // We strip the tags because the component expects only one paragraph of
        // text and the field is using a text format which adds paragraph tags.
        '#markup' => strip_tags($entity->get('oe_news_summary')->value, '<strong><a><em>'),
      ];
    }

    $timestamp = $entity->get('oe_news_publication_date')->value;
    $metadata['metas'] = [
      $this->t('News'),
      $this->dateFormatter->format($timestamp, 'oe_theme_news_date'),
    ];

    return $metadata;
  }

}
