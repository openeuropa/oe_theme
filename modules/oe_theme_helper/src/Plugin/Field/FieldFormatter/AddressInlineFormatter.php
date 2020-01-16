<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\address\AddressInterface;
use Drupal\address\Plugin\Field\FieldFormatter\AddressDefaultFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'oe_theme_helper_address_inline' formatter.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_address_inline",
 *   label = @Translation("Inline address"),
 *   field_types = {
 *     "address",
 *   },
 * )
 */
class AddressInlineFormatter extends AddressDefaultFormatter implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'delimiter' => ', ',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['delimiter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Delimiter'),
      '#default_value' => $this->getSetting('delimiter'),
      '#description' => $this->t('Specify delimiter between address items.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings_summary = parent::settingsSummary();
    $example_address_items = [
      'Rue de la Loi',
      '56',
      '1000',
      'Brussels',
    ];
    $settings_summary[] = implode($this->getSetting('delimiter'), $example_address_items);
    return $settings_summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewElement($item, $langcode);
    }

    return $elements;
  }

  /**
   * Builds a renderable array for a single address item.
   *
   * @param \Drupal\address\AddressInterface $address
   *   The address.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array.
   */
  protected function viewElement(AddressInterface $address, $langcode) {
    $country_code = $address->getCountryCode();
    $countries = $this->countryRepository->getList();
    $address_format = $this->addressFormatRepository->get($country_code);
    $values = $this->getValues($address, $address_format);
    $values['country'] = $countries[$country_code];
    return [
      '#theme' => 'oe_theme_helper_address_inline',
      '#address' => $address,
      '#address_items' => $values,
      '#address_delimiter' => $this->getSetting('delimiter'),
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
        ],
      ],
    ];
  }

}
