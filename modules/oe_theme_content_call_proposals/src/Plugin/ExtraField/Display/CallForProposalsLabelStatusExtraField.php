<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_call_proposals\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapper;

/**
 * Display Call for proposals status as label.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_call_proposals_label_status",
 *   label = @Translation("Status as a label"),
 *   bundles = {
 *     "node.oe_call_proposals",
 *   },
 *   visible = true
 * )
 */
class CallForProposalsLabelStatusExtraField extends CallForProposalsStatusExtraField {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Status as a label');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build = parent::viewElements($entity);
    $entity = CallForProposalsNodeWrapper::getInstance($entity);
    $build['#theme'] = 'oe_theme_helper_call_label_status';
    $build['#label'] = $this->t('Call status: @label', ['@label' => $build['#label']]);
    $build['#name'] = $entity->getStatus();

    return $build;
  }

}
