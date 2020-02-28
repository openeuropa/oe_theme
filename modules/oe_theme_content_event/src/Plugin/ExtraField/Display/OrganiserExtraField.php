<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\rdf_skos\Entity\ConceptInterface;

/**
 * Extra field displaying organiser information on events.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_organiser",
 *   label = @Translation("Organiser"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class OrganiserExtraField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Organiser');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $is_internal = (bool) $entity->get('oe_event_organiser_is_internal')->value;

    if (!$is_internal) {
      // If the organiser is not internal and we have an organiser name, use
      // that. Without an organiser name we return an empty array to indicate
      // an empty field.
      return !$entity->get('oe_event_organiser_name')->isEmpty() ? ['#markup' => $entity->get('oe_event_organiser_name')->value] : [];
    }

    // If the organiser is internal and not empty, show it.
    if ($entity->get('oe_event_organiser_internal')->isEmpty()) {
      return [];
    }

    $organiser = $entity->get('oe_event_organiser_internal')->entity;
    if (!$organiser instanceof ConceptInterface) {
      return [];
    }

    $build = [
      '#markup' => $organiser->label(),
    ];

    CacheableMetadata::createFromObject($organiser)->applyTo($build);

    return $build;
  }

}
