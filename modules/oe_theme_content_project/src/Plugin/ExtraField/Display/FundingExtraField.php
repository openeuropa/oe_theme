<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_project\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field displaying Funding and Proposals Projecr fields.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_project_funding",
 *   label = @Translation("Funding"),
 *   bundles = {
 *     "node.oe_project",
 *   },
 *   visible = true
 * )
 */
class FundingExtraField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    foreach ($entity->oe_project_funding_programme->getValue() as $item) {
      $build['#items'][] = [
        'fieldlabel' => $entity->oe_project_calls->getFieldDefinition()->getLabel(),
        'link' => [
          'text' => \Drupal::entityTypeManager()->getStorage('skos_concept')->load($item['target_id'])->get('pref_label')->value,
          'url' => $item['target_id'],
        ],
      ];
    }
    foreach ($entity->oe_project_calls->getValue() as $item) {
      $build['#items'][] = [
        'fieldlabel' => $entity->oe_project_calls->getFieldDefinition()->getLabel(),
        'link' => [
          'text' => $item['title'],
          'url' => $item['uri'],
        ],
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Funding');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'above';
  }

}
