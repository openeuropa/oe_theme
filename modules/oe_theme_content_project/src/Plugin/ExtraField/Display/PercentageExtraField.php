<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_project\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Extra field displaying eu budget percentage on projects.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_project_percentage",
 *   label = @Translation("Percentage"),
 *   bundles = {
 *     "node.oe_project",
 *   },
 *   visible = true
 * )
 */
class PercentageExtraField extends ProjectExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    // Add space as label so the value aligns with the other fields.
    return ' ';
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // Compute budget percentage field value.
    $budget = $entity->get('oe_project_budget')->value;
    $budget_eu = $entity->get('oe_project_budget_eu')->value;
    $percentage = $this->getPercentage((float) $budget, (float) $budget_eu);
    $build = [
      '#plain_text' => $this->t("@percentage% of the overall budget", ["@percentage" => $percentage]),
    ];
    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($entity);
    $cache->applyTo($build);

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
  private function getPercentage(float $total, float $part): float {
    $percentage = 0;

    if ($total > 0 && $part > 0) {
      $percentage = round(100 * $part / $total, 0);
    }

    return $percentage;
  }

}
