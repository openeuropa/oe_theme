<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_call_tenders\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_call_tenders\CallForTendersNodeWrapper;

/**
 * Display call for tenders status as a label.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_call_tenders_label_status",
 *   label = @Translation("Status as a label"),
 *   bundles = {
 *     "node.oe_call_tenders",
 *   },
 *   visible = true
 * )
 */
class CallForTendersLabelStatusExtraField extends CallForTendersStatusExtraField {

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
    $entity = CallForTendersNodeWrapper::getInstance($entity);
    $build['#theme'] = 'oe_theme_helper_call_label_status';
    $build['#label'] = $this->t('Call status: @label', ['@label' => $build['#label']]);
    $build['#name'] = $entity->getStatus();
    return $build;
  }

}
