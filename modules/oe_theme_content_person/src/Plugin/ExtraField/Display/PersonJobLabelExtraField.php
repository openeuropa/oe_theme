<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_person\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Display Person job label.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_person_job_label",
 *   label = @Translation("Person job label"),
 *   bundles = {
 *     "oe_person_job.oe_default",
 *   },
 *   visible = true
 * )
 */
class PersonJobLabelExtraField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Person job label');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // Do not show Person job label at all if the description field is empty.
    if ($entity->get('oe_description')->isEmpty()) {
      $this->isEmpty = TRUE;
    }
    return ['#markup' => $entity->label()];
  }

}
