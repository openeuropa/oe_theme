<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_content_entity_venue\Entity\VenueInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying event location.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_location",
 *   label = @Translation("Location"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class LocationExtraField extends EventExtraFieldBase {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * LocationExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
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
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Where');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build = ['#theme' => 'oe_theme_content_event_location'];

    // If the event is marked as online only, we render the 'Online only'
    // string, otherwise we fallback to the 'Venue' field.
    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder */
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    if ($entity->get('oe_event_online_only')->value) {
      $build['#location'] = $view_builder->viewField($entity->get('oe_event_online_only'), [
        'label' => 'hidden',
        'type' => 'boolean',
        'settings' => [
          'format' => 'custom',
          'format_custom_true' => $this->t('Online only'),
        ],
      ]);
      return $build;
    }

    if (!$entity->get('oe_event_venue')->isEmpty() && $entity->get('oe_event_venue')->entity instanceof VenueInterface) {
      /** @var \Drupal\oe_content_entity_venue\Entity\VenueInterface $venue */
      $venue = $entity->get('oe_event_venue')->entity;
      $venue = $this->entityRepository->getTranslationFromContext($venue);
      $access = $venue->access('view', NULL, TRUE);
      if (!$access->isAllowed()) {
        return [];
      }
      $build['#location'] = $this->entityTypeManager->getViewBuilder('oe_venue')->view($venue);
      return $build;
    }
    return [];
  }

}
