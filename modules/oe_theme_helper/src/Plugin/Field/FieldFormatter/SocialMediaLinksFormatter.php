<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;

/**
 * Display typed links as social media.
 *
 * This formatter assumes that link categories will be compatible with
 * media service names used in the "Social media links: horizontal" pattern.
 *
 * @see templates/patterns/social_media_links/social_media_links_horizontal.ui_patterns.yml
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_social_media_links_formatter",
 *   label = @Translation("Social media links"),
 *   description = @Translation("Display typed links as social media."),
 *   field_types = {
 *     "typed_link"
 *   }
 * )
 */
class SocialMediaLinksFormatter extends LinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (!$items->count()) {
      return [];
    }

    $pattern = [
      '#type' => 'pattern',
      '#id' => 'social_media_links_horizontal',
      '#fields' => [
        'title' => $this->t('Social media'),
        'links' => [],
      ],
    ];

    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $pattern['#fields']['links'][] = [
        'service' => $item->link_type,
        'label' => $elements[$delta]['#title'],
        'url' => $elements[$delta]['#url']->toString(),
      ];
    }

    return [$pattern];
  }

}
