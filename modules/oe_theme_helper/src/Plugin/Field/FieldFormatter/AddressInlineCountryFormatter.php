<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\address\Plugin\Field\FieldFormatter\AddressDefaultFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Format country inline with locale format and a configurable separator.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_address_country_inline",
 *   label = @Translation("Inline address country only"),
 *   field_types = {
 *     "address",
 *   },
 * )
 */
class AddressInlineCountryFormatter extends AddressDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'delimiter' => ', ',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $form['delimiter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Delimiter'),
      '#default_value' => $this->getSetting('delimiter'),
      '#description' => $this->t('Specify delimiter between country items.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return [
      $this->t('Delimiter: @delimiter', [
        '@delimiter' => $this->getSetting('delimiter'),
      ]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $countries = [];
    foreach ($items as $delta => $item) {
      $country_code = $item->getCountryCode();
      $country_list = $this->countryRepository->getList($langcode);
      $current_country = $country_list[$country_code] ?? $country_code;
      // If the list of countries already contains the current one, skip it.
      if (in_array($current_country, $countries)) {
        continue;
      }
      $countries[$delta] = $current_country;
    }

    if (empty($countries)) {
      return [];
    }

    return [
      [
        '#markup' => implode($this->getSetting('delimiter'), $countries),
        '#cache' => [
          'contexts' => [
            'languages:' . LanguageInterface::TYPE_INTERFACE,
          ],
        ],
      ],
    ];
  }

}
