<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_publication\Plugin\PageHeaderMetadata;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Drupal\rdf_skos\Plugin\Field\SkosConceptReferenceFieldItemList;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page header metadata for the OpenEuropa Publication content entity.
 *
 * @PageHeaderMetadata(
 *   id = "publication_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Publication content type"),
 *   weight = -1
 * )
 */
class PublicationContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Creates a new PublicationContentType object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);
    $this->entityRepository = $entity_repository;
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
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_publication';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $node = $this->getNode();

    $metadata = parent::getMetadata();
    $metadata['metas'][] = $this->getSeparatedSkosMeta($node->get('oe_publication_type'));

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
  protected function getSeparatedSkosMeta(SkosConceptReferenceFieldItemList $items): string {
    $list = [];
    foreach ($items as $item) {
      $entity = $item->entity;
      $list[] = $this->entityRepository->getTranslationFromContext($entity)->label();
    }
    return implode(' | ', $list);
  }

}
