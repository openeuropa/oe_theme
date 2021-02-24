<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_person\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Display Job's role.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_person_job_role",
 *   label = @Translation("Job role"),
 *   bundles = {
 *     "oe_person_job.default",
 *   },
 *   visible = true
 * )
 */
class JobRoleExtraField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Job role');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $role_name = $entity->label();

    if ($entity->get('oe_acting')->value) {
      $role_name = $this->t('(Acting) @role', ['@role' => $role_name]);
    }

    return ['#markup' => $role_name];
  }

}
