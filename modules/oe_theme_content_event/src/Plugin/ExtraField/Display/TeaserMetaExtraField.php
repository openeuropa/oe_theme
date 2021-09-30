<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying meta information for the event teaser.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_teaser_meta",
 *   label = @Translation("Teaser meta"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = false
 * )
 */
class TeaserMetaExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * TeaserMetaExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Teaser meta');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $elements = [];

    // Add the event type.
    /** @var \Drupal\rdf_skos\Entity\ConceptInterface $type */
    $type = $entity->get('oe_event_type')->entity;
    if ($type) {
      $type = $this->entityRepository->getTranslationFromContext($type);
      $elements[] = ['#markup' => $type->label()];
      CacheableMetadata::createFromObject($type)
        ->applyTo($elements);
    }

    // Add the event status, only if it doesn't go as planned.
    $status = $entity->get('oe_event_status')->value;
    if ($status !== 'as_planned') {
      $provider = $entity->get('oe_event_status')->getFieldDefinition()
        ->getFieldStorageDefinition()
        ->getOptionsProvider('value', $entity);
      $elements[] = ['#markup' => $provider->getPossibleOptions()[$status]];
    }

    return $elements;
  }

}
