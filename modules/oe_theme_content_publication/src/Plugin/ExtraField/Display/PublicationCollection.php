<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_publication\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays publication collections.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_publication_collection",
 *   label = @Translation("Part of collection"),
 *   bundles = {
 *     "node.oe_publication",
 *   },
 *   visible = true
 * )
 */
class PublicationCollection extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The field label.
   *
   * @var string
   */
  protected $label;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * PublicationCollection constructor.
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
   *   The entity repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build = [];
    /** @var \Drupal\node\Entity\Node[] $collections */
    $collections = $this->getReferencingCollections($entity);

    if (empty($collections)) {
      return $build;
    }

    $build = [
      '#theme' => 'oe_theme_content_publication_collections',
    ];

    $cache = CacheableMetadata::createFromRenderArray($build);

    $items = [];
    foreach ($collections as $collection) {
      $cache->addCacheableDependency($collection);

      // Run access checks on the node.
      $access = $collection->access('view', NULL, TRUE);
      $cache->addCacheableDependency($access);
      if (!$access->isAllowed()) {
        continue;
      }

      // Get the current translation.
      $collection = $this->entityRepository->getTranslationFromContext($collection);

      $items[] = [
        '#type' => 'link',
        '#title' => $collection->label(),
        '#url' => $collection->toUrl(),
        '#attributes' => [
          'class' => ['ecl-link', 'ecl-link--standalone'],
        ],
      ];
    }

    // Set the label of the field.
    if (count($items) > 1) {
      $this->label = $this->t('Part of collections');
    }
    else {
      $this->label = $this->t('Part of collection');
    }

    $build['#items'] = $items;

    $cache->applyTo($build);

    return $build;
  }

  /**
   * Get the publication collections that are referencing other publications.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The publication entity.
   *
   * @return array
   *   The loaded publication entities.
   */
  protected function getReferencingCollections(EntityInterface $entity): array {
    // Load all entities that have a reference to the given entity.
    $entity_type_storage = $this->entityTypeManager->getStorage('node');
    $query = $entity_type_storage->getQuery();
    $query->condition('oe_publication_publications.target_id', $entity->id());

    $ids = $query->accessCheck()->execute();
    if ($ids) {
      return $entity_type_storage->loadMultiple($ids);
    }

    return [];
  }

}
