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
 * Display EU contribution and its percentage of the total budget.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_project_percentage",
 *   label = @Translation("EU contribution percentage"),
 *   bundles = {
 *     "node.oe_project",
 *   },
 *   visible = true
 * )
 */
class PercentageExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilder
   */
  protected $viewBuilder;

  /**
   * EventExtraFieldBase constructor.
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
    return $this->t('EU contribution');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    if ($entity->get('oe_project_budget_eu')->isEmpty()) {
      return [];
    }
    $build = [];
    $build[] = $this->viewBuilder->viewField($entity->get('oe_project_budget_eu'), [
      'label' => 'hidden',
      'type' => 'number_decimal',
      'settings' => [
        'thousand_separator' => '.',
        'decimal_separator' => '.',
        'scale' => 0,
        'prefix_suffix' => TRUE,
      ],
    ]);

    // Return only EU contribution if budget is empty.
    if ($entity->get('oe_project_budget')->isEmpty()) {
      return $build;
    }

    // Compute budget percentage field value.
    $budget = $entity->get('oe_project_budget')->value;
    $budget_eu = $entity->get('oe_project_budget_eu')->value;
    $percentage = $this->getPercentage((float) $budget, (float) $budget_eu);
    $build[] = [
      '#markup' => '<div class="ecl-u-mt-m">' . $this->t("@percentage% of the overall budget", ["@percentage" => $percentage]) . '</div>',
    ];

    return $build;
  }

  /**
   * Gets the percentage of total, without decimals.
   *
   * If input values are not greater that 0, returns 0.
   *
   * @param float $total
   *   The total value.
   * @param float $part
   *   The percentage value.
   *
   * @return float
   *   Percentage value.
   */
  protected function getPercentage(float $total, float $part): float {
    $percentage = 0;

    if ($total > 0 && $part > 0) {
      $percentage = round(100 * $part / $total, 0);
    }

    return $percentage;
  }

}
