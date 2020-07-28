<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays typed links as social media.
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
class SocialMediaLinksFormatter extends SocialMediaBaseLinkFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'title' => 'Social media',
      'variant' => 'horizontal',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if (!empty($settings['title'])) {
      $summary[] = $this->t('Block title: @title', ['@title' => $settings['title']]);
    }

    if (!empty($settings['variant'])) {
      $summary[] = $this->t('Pattern variant: @variant', ['@variant' => $settings['variant']]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block title'),
      '#default_value' => $this->getSetting('title'),
      '#required' => TRUE,
    ];

    $elements['variant'] = [
      '#title' => $this->t('Variant'),
      '#type' => 'select',
      '#options' => [
        'horizontal' => $this->t('Horizontal'),
        'vertical' => $this->t('Vertical'),
      ],
      '#default_value' => $this->getSetting('variant'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (!$items->count()) {
      return [];
    }

    $pattern = [
      '#type' => 'pattern',
      '#id' => 'social_media_links_' . $this->getSetting('variant'),
      '#fields' => [
        'title' => $this->getSetting('title'),
        'links' => [],
      ],
    ];

    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $pattern['#fields']['links'][] = [
        'service' => $item->link_type,
        'label' => $elements[$delta]['#title'],
        'url' => $elements[$delta]['#url'],
      ];
    }

    $output = [
      '#theme' => 'oe_theme_helper_social_media_links',
      '#content' => $pattern,
    ];
    return [$output];
  }

}
