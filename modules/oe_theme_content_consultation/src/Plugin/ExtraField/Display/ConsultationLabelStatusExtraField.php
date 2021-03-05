<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_consultation\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_consultation\ConsultationNodeWrapper;

/**
 * Display Consultation status as label.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_consultation_label_status",
 *   label = @Translation("Status as a label"),
 *   bundles = {
 *     "node.oe_consultation",
 *   },
 *   visible = true
 * )
 */
class ConsultationLabelStatusExtraField extends ConsultationStatusExtraField {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Status as a label');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity): array {
    $build = parent::viewElements($entity);
    $entity = ConsultationNodeWrapper::getInstance($entity);
    $build['#theme'] = 'oe_theme_helper_call_label_status';
    $build['#label'] = $this->t('Status: @label', ['@label' => $build['#label']]);
    $build['#name'] = $entity->getStatus();

    return $build;
  }

}
