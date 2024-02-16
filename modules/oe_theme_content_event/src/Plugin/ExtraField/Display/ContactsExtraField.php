<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Extra field displaying contacts information on events.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_contacts",
 *   label = @Translation("Contacts"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class ContactsExtraField extends EventExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Contacts');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $cacheability = new CacheableMetadata();

    // Return an empty array if empty, so the field can be considered empty.
    if ($entity->get('oe_event_contact')->isEmpty()) {
      return [];
    }

    // We get here renderable arrays for contact entities.
    // We also divide them by bundle, so we can display them in a grid layout.
    $build = [
      '#theme' => 'oe_theme_content_event_contacts',
      '#general' => [],
      '#press' => [],
    ];

    /** @var \Drupal\oe_content_entity_contact\Entity\ContactInterface $contact */
    foreach ($entity->get('oe_event_contact')->referencedEntities() as $contact) {
      $bundle = $contact->bundle();

      $access = $contact->access('view', NULL, TRUE);
      $cacheability->addCacheableDependency($access);

      if (!$access->isAllowed()) {
        continue;
      }

      // We only handle 'oe_press' or 'oe_general' bundles.
      if (!in_array($bundle, ['oe_general', 'oe_press'])) {
        continue;
      }

      $key = ($bundle === 'oe_general') ? '#general' : '#press';
      $build[$key][] = $this->entityTypeManager->getViewBuilder('oe_contact')->view($contact);
      $cacheability->addCacheableDependency($contact);
    }

    $cacheability->applyTo($build);
    return $build;
  }

}
