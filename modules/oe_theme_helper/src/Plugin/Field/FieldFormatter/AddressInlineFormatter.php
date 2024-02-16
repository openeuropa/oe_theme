<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use CommerceGuys\Addressing\Locale;
use Drupal\address\AddressInterface;
use Drupal\address\Plugin\Field\FieldFormatter\AddressDefaultFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Format an address inline with locale format and a configurable separator.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_address_inline",
 *   label = @Translation("Inline address"),
 *   field_types = {
 *     "address",
 *   },
 * )
 */
class AddressInlineFormatter extends AddressDefaultFormatter {

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
      '#description' => $this->t('Specify delimiter between address items.'),
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
    $countries = $this->countryRepository->getList($langcode);
    $address_format = $this->addressFormatRepository->get($country_code);
    $values = $this->getValues($address, $address_format);

    $address_elements['%country'] = $countries[$country_code];
    foreach ($address_format->getUsedFields() as $field) {
      $address_elements['%' . $field] = $values[$field];
    }

    if (Locale::matchCandidates($address_format->getLocale(), $address->getLocale())) {
      $format_string = '%country' . "\n" . $address_format->getLocalFormat();
    }
    else {
      $format_string = $address_format->getFormat() . "\n" . '%country';
    }
    /*
     * Remove extra characters from address format since address fields are
     * optional.
     *
     * @see \CommerceGuys\Addressing\AddressFormat\AddressFormatRepository::getDefinitions()
     */
    $format_string = str_replace([',', ' - ', '/'], "\n", $format_string);

    $items = $this->extractAddressItems($format_string, $address_elements);

    return [
      '#theme' => 'oe_theme_helper_address_inline',
      '#address' => $address,
      '#address_items' => $items,
      '#address_delimiter' => $this->getSetting('delimiter'),
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
        ],
      ],
    ];
  }

  /**
   * Extract address items from a format string and replace placeholders.
   *
   * @param string $string
   *   The address format string, containing placeholders.
   * @param array $replacements
   *   An array of address items.
   *
   * @return array
   *   The exploded lines.
   */
  protected function extractAddressItems(string $string, array $replacements): array {
    // Make sure the replacements don't have any unneeded newlines.
    array_walk($replacements, function (&$value) {
      $value = trim($value ?? '');
    });
    $string = strtr($string, $replacements);
    // Remove noise caused by empty placeholders.
    $lines = explode("\n", $string);
    foreach ($lines as $index => $line) {
      // Remove leading punctuation, excess whitespace.
      $line = trim(preg_replace('/^[-,]+/', '', $line, 1));
      $line = preg_replace('/\s\s+/', ' ', $line);
      $lines[$index] = $line;
    }
    // Remove empty lines.
    return array_filter($lines);
  }

}
