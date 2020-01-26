<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
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
    $settings = $this->getSettings();
    $summary = [];

    if (!empty($settings['trim_length'])) {
      $summary[] = t('Link text trimmed to @limit characters', ['@limit' => $settings['trim_length']]);
    }
    else {
      $summary[] = t('Link text not trimmed');
    }

    if (!empty($settings['title'])) {
      $summary[] = t('Block title: @title', ['@title' => $settings['title']]);
    }

    if (!empty($settings['variant'])) {
      $summary[] = t('Pattern variant: @variant', ['@variant' => $settings['variant']]);
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
      '#title' => t('Block title'),
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

    // Hide following settings as their behaviour is controlled by the pattern.
    $elements['url_only']['#access'] = FALSE;
    $elements['url_plain']['#access'] = FALSE;
    $elements['rel']['#access'] = FALSE;
    $elements['target']['#access'] = FALSE;

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
        'url' => $elements[$delta]['#url']->toString(),
      ];
    }

    return [$pattern];
  }

}
