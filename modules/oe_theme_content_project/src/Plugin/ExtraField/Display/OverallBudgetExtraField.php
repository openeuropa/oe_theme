<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_project\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display overall budget.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_project_budget",
 *   label = @Translation("Overall budget"),
 *   bundles = {
 *     "node.oe_project",
 *   },
 *   visible = true
 * )
 */
class OverallBudgetExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilder
   */
  protected $viewBuilder;

  /**
   * OverallBudgetExtraField constructor.
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
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
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
    return $this->t('Overall budget');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    if ($entity->get('oe_project_budget')->isEmpty() && $entity->get('oe_project_eu_budget')->isEmpty()) {
      return [];
    }
    $build = [];

    $display_options = [
      'label' => 'hidden',
      'type' => 'number_decimal',
      'settings' => [
        'thousand_separator' => ' ',
        'decimal_separator' => '.',
        'scale' => 0,
        'prefix_suffix' => TRUE,
      ],
    ];
    if ($entity->get('oe_project_eu_contrib')->isEmpty()) {
      // Fallback to old field.
      $build[] = $this->viewBuilder->viewField($entity->get('oe_project_budget'), $display_options);
    }
    else {
      // Render new field value.
      $build[] = $this->viewBuilder->viewField($entity->get('oe_project_eu_budget'), $display_options);
    }

    return $build;
  }

}
