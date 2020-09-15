<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_tender\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Display call for tender status, formatter as a .
 *
 * @ExtraFieldDisplay(
 *   id = "oe_tender_label_status",
 *   label = @Translation("Status as a label"),
 *   bundles = {
 *     "node.oe_tender",
 *   },
 *   visible = true
 * )
 */
class TenderLabelStatusExtraField extends TenderStatusExtraField {

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
    $build['#theme'] = 'oe_theme_content_tender_label_status';
    $build['#label'] = $this->t('Call status: @label', ['@label' => $build['#label']]);
    return $build;
  }

}
