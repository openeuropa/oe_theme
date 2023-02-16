<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_person\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display list of person job entities, as an horizontal field list.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_person_job_list",
 *   label = @Translation("Person job list"),
 *   bundles = {
 *     "node.oe_person",
 *   },
 *   visible = true
 * )
 */
class PersonJobListExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

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
   * PersonJobListExtraField constructor.
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
    return $this->t('Person job list');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    if ($entity->get('oe_person_jobs')->isEmpty()) {
      return [];
    }

    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->addCacheableDependency($entity);
    $job_list = [
      '#theme' => 'oe_theme_content_person_job_list',
      '#items' => [],
    ];
    // Prepare person jobs to be shown in the field list pattern.
    $view_builder = $this->entityTypeManager->getViewBuilder('oe_person_job');
    $single_person_job = count($entity->get('oe_person_jobs')->referencedEntities()) === 1;
    foreach ($entity->get('oe_person_jobs')->referencedEntities() as $person_job) {
      // Retrieve the translation of the person job entity.
      $person_job = $this->entityRepository->getTranslationFromContext($person_job);

      $body = '';
      if (!$person_job->get('oe_description')->isEmpty()) {
        $body = $view_builder->viewField($person_job->get('oe_description'), [
          'label' => 'hidden',
        ]);
      }
      else {
        // Don't add an item if Person Job has an empty description field.
        $cacheable_metadata->addCacheableDependency($person_job);
        continue;
      }
      $job_list['#items'][] = [
        'label' => $single_person_job ? NULL : $person_job->label(),
        'body' => $body,
      ];
      $cacheable_metadata->addCacheableDependency($person_job);
    }
    // Hide the job list if there are no items.
    if (empty($job_list['#items'])) {
      return [];
    }
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'field_list',
      '#variant' => 'horizontal',
      '#fields' => [
        'items' => [
          [
            'label' => $this->t('Responsibilities'),
            'body' => $job_list,
          ],
        ],
      ],
    ];
    $cacheable_metadata->applyTo($pattern);
    return $pattern;
  }

}
