<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_person\Plugin\PageHeaderMetadata;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page header metadata for the OpenEuropa "Person" content type.
 *
 * @PageHeaderMetadata(
 *   id = "oe_person_content_type",
 *   label = @Translation("Metadata extractor for the OE Person content type"),
 *   weight = -1
 * )
 */
class PersonContentType extends NodeViewRoutesBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new PersonContentType object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_person';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $node = $this->getNode();
    $metadata = parent::getMetadata();

    if (!$node->get('oe_person_jobs')->isEmpty()) {
      $meta = $this->entityTypeManager
        ->getViewBuilder('node')
        ->viewField($node->get('oe_person_jobs'), [
          'label' => 'hidden',
          'type' => 'entity_reference_revisions_label',
        ]);
      $metadata['metas'] = [$meta];
    }

    return $metadata;
  }

}
