<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Display short title, if available. If not, fallback to default title.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_helper_short_title_with_fallback",
 *   label = @Translation("Short title with fallback"),
 *   bundles = {
 *     "node.*"
 *   },
 *   visible = false
 * )
 */
class ShortTitleWithFallbackExtraField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Short title with fallback');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $elements = [];

    // If no short title field is available, then fallback to default title.
    if (!$entity->hasField('oe_content_short_title')) {
      $elements[] = ['#markup' => $entity->label()];
    }
    // Display short title only if not empty.
    $elements[] = [
      '#markup' => !$entity->get('oe_content_short_title')->isEmpty() ? $entity->get('oe_content_short_title')->value : $entity->label(),
    ];

    return $elements;
  }

}
