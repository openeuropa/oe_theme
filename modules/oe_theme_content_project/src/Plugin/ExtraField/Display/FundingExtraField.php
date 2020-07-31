<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_project\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Extra field displaying Funding and Proposals Project fields.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_project_funding",
 *   label = @Translation("Funding"),
 *   bundles = {
 *     "node.oe_project",
 *   },
 *   visible = true
 * )
 */
class FundingExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * FundingExtraField constructor.
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
  public function viewElements(ContentEntityInterface $entity) {
    $build = [];

    /** @var \Drupal\rdf_skos\Plugin\Field\FieldType\SkosConceptEntityReferenceItem $item */
    foreach ($entity->get('oe_project_funding_programme')->getIterator() as $item) {
      if ($item->isEmpty() || empty($item->entity)) {
        continue;
      }

      $pattern = [
        '#type' => 'pattern',
        '#id' => 'list_item',
        '#variant' => 'default',
        '#fields' => [
          'meta' => [
            'label' => $item->getFieldDefinition()->getLabel(),
          ],
          'title' => $item->entity->pref_label->view('skos_concept_entity_reference_label'),
        ],
      ];
      $concept_schemes = $this->entityTypeManager->getStorage('skos_concept')
        ->loadMultiple([$item->entity->id()]);
      $cacheability = new CacheableMetadata();
      $cacheability->addCacheableDependency(array_pop($concept_schemes));
      $cacheability->applyTo($pattern);
      $build[] = $pattern;
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $item */
    foreach ($entity->get('oe_project_calls')->getIterator() as $item) {
      if ($item->isEmpty()) {
        continue;
      }

      $view = $item->view(['type' => 'link']);
      $build[] = [
        '#type' => 'pattern',
        '#id' => 'list_item',
        '#variant' => 'default',
        '#fields' => [
          'meta' => [
            'label' => $item->getFieldDefinition()->getLabel(),
          ],
          'title' => $view['#title'],
          'url' => $view['#url'],
        ],
      ];
    }

    return [$build];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Funding');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'above';
  }

}
