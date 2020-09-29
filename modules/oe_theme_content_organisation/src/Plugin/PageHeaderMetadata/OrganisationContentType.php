<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_organisation\Plugin\PageHeaderMetadata;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page header metadata for the OpenEuropa Organisation content entity.
 *
 * @PageHeaderMetadata(
 *   id = "organisation_content_type",
 *   label = @Translation("Metadata extractor for the OE Organisation Content content type"),
 *   weight = -1
 * )
 */
class OrganisationContentType extends NodeViewRoutesBase {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Creates a new NodeViewRouteBase object.
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

    return $node && $node->bundle() === 'oe_organisation';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();
    $node = $this->getNode();
    $metadata['metas'] = [];

    // Get organisation type, it can be either EU or non-EU.
    if (!$node->get('oe_organisation_eu_org_type')->isEmpty()) {
      $entity = $node->get('oe_organisation_eu_org_type')->entity;
      $metadata['metas'][] = $this->entityRepository->getTranslationFromContext($entity)->label();
    }
    if (!$node->get('oe_organisation_non_eu_org_type')->isEmpty()) {
      $entity = $node->get('oe_organisation_non_eu_org_type')->entity;
      $metadata['metas'][] = $this->entityRepository->getTranslationFromContext($entity)->label();
    }

    // Add acronym, if any.
    if (!$node->get('oe_organisation_acronym')->isEmpty()) {
      $metadata['metas'][] = $node->get('oe_organisation_acronym')->value;
    }

    return $metadata;
  }

}
