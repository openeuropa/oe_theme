<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Extra field displaying event details on teasers.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_teaser_details",
 *   label = @Translation("Teaser details"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = false
 * )
 */
class TeaserDetailsExtraField extends DetailsExtraField {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Teaser details');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build = [
      '#type' => 'pattern',
      '#id' => 'icons_with_text',
      '#fields' => [
        'items' => [],
        'compact' => TRUE,
      ],
    ];

    $this->addRenderableLocation($build, $entity);
    $this->addRenderableOnlineType($build, $entity);
    // Override default icon size.
    foreach ($build['#fields']['items'] as $key => $item) {
      $build['#fields']['items'][$key]['size'] = 'xs';
    }

    return $build;
  }

}
