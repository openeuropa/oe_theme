<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\oe_content_entity_venue\Entity\VenueInterface;

/**
 * Extra field displaying event details as a list of icons and text.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_details",
 *   label = @Translation("Details"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class DetailsExtraField extends EventExtraFieldBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Details');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // The pattern will take care of not displaying empty items.
    return [
      '#type' => 'pattern',
      '#id' => 'icons_with_text',
      '#fields' => [
        'items' => [
          [
            'icon' => 'file',
            'text' => $this->getRenderableSubject($entity),
          ],
          [
            'icon' => 'calendar',
            'text' => $this->getRenderableDates($entity),
          ],
          [
            'icon' => 'location',
            'text' => $this->getRenderableLocation($entity),
          ],
          [
            'icon' => 'livestreaming',
            'text' => $entity->get('oe_event_online_type')->isEmpty() ? '' : $this->t('Live streaming available'),
          ],
        ],
      ],
    ];
  }

  /**
   * Get the event subject as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableSubject(ContentEntityInterface $entity): array {
    return $this->entityTypeManager->getViewBuilder('oe_contact')->viewField($entity->get('oe_subject'), [
      'label' => 'hidden',
      'settings' => [
        'link' => FALSE,
      ],
    ]);
  }

  /**
   * Get the event dates as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableDates(ContentEntityInterface $entity): array {
    return $this->entityTypeManager->getViewBuilder('oe_contact')->viewField($entity->get('oe_event_dates'), [
      'label' => 'hidden',
      'type' => 'daterange_default',
      'settings' => [
        'format_type' => 'oe_event_date_hour',
        'separator' => $this->t('to'),
      ],
    ]);
  }

  /**
   * Get the event location as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableLocation(ContentEntityInterface $entity): array {
    if ($entity->get('oe_event_venue')->isEmpty()) {
      return [];
    }

    $venue = $entity->get('oe_event_venue')->entity;
    if (!$venue instanceof VenueInterface) {
      return [];
    }

    $build = [];
    CacheableMetadata::createFromObject($venue)->applyTo($build);

    // If address is empty only return cache metadata, so it can bubble up.
    if ($venue->get('oe_address')->isEmpty()) {
      return $build;
    }

    // Only display locality and country, inline.
    $renderable = $this->entityTypeManager->getViewBuilder('oe_venue')->viewField($venue->get('oe_address'));
    $renderable[0]['locality']['#value'] .= ',&nbsp;';

    $build['locality'] = $renderable[0]['locality'];
    $build['country'] = $renderable[0]['country'];
    return $build;
  }

}
