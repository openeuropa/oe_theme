<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_person\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity_type.manager')
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

    $pattern = [
      '#type' => 'pattern',
      '#id' => 'field_list',
      '#variant' => 'horizontal',
      '#fields' => [
        'items' => [],
      ],
      '#prefix' => '<div class="ecl-u-border-top ecl-u-border-color-grey-15 ecl-u-pt-m">',
      '#suffix' => '</div>',
    ];

    $cacheable_metadata = CacheableMetadata::createFromRenderArray($pattern);
    $cacheable_metadata->addCacheableDependency($entity);

    // Prepare person jobs to be shown in the field list pattern.
    $view_builder = $this->entityTypeManager->getViewBuilder('oe_person_job');
    foreach ($entity->get('oe_person_jobs')->referencedEntities() as $person_job) {
      // Body has to be filled with at least empty space. Otherwise whole line
      // will be hidden.
      $body = ' ';
      if (!$person_job->get('oe_description')->isEmpty()) {
        $body = $view_builder->viewField($person_job->get('oe_description'), [
          'label' => 'hidden',
        ]);
      }
      $pattern['#fields']['items'][] = [
        'label' => $person_job->label(),
        'body' => $body,
      ];
      $cacheable_metadata->addCacheableDependency($person_job);
    }

    $cacheable_metadata->applyTo($pattern);
    return $pattern;
  }

}
