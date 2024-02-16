<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Displays typed links as a list of social icons.
 *
 * This formatter assumes that link categories will be compatible with
 * media service names used in the "Social media links: horizontal" pattern.
 *
 * @see templates/patterns/social_icon/social_icon.ui_patterns.yml
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_social_media_icons_list_formatter",
 *   label = @Translation("Social media icons list"),
 *   description = @Translation("Display typed links as list of social icons."),
 *   field_types = {
 *     "typed_link"
 *   }
 * )
 */
class SocialMediaIconsListFormatter extends SocialMediaBaseLinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (!$items->count()) {
      return [];
    }

    $list_items = [];
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $pattern = [
        '#type' => 'pattern',
        '#id' => 'social_icon',
        '#fields' => [
          'service' => $item->link_type,
          'label' => $elements[$delta]['#title'],
          'url' => $elements[$delta]['#url'],
        ],
      ];

      $list_items[] = $pattern;
    }

    $output = [
      '#theme' => 'oe_theme_helper_social_media_icons_list',
      '#items' => $list_items,
    ];
    return [$output];
  }

}
