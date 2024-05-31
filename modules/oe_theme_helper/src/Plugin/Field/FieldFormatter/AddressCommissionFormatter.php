<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\address\AddressInterface;
use Drupal\address\Plugin\Field\FieldFormatter\AddressDefaultFormatter;
use Drupal\Core\Render\Element;

/**
 * Format an address with locale format but divide only with 3 lines.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_address_commission",
 *   label = @Translation("Commission address format"),
 *   field_types = {
 *     "address",
 *   },
 * )
 */
class AddressCommissionFormatter extends AddressDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  protected function viewElement(AddressInterface $address, $langcode) {
    $element = parent::viewElement($address, $langcode);
    foreach (Element::children($element) as $key) {
      if (!empty($element[$key]['#value']) && $element[$key]['#type'] === 'html_tag' && $element[$key]['#tag'] === 'span') {
        $element[$key]['#value'] .= ' ';
      }
    }
    return $element;
  }

  /**
   * Replaces placeholders in the given string.
   *
   * @param string $string
   *   The string containing the placeholders.
   * @param array $replacements
   *   An array of replacements keyed by their placeholders.
   *
   * @return string
   *   The processed string.
   */
  public static function replacePlaceholders($string, array $replacements) {
    $devided_lines = array_filter(explode('%', $string));
    array_walk($devided_lines, function (&$line) {
      $line = str_replace("\n", '', trim($line));
    });

    $reformatted_lines = [
      'address' => [],
      'city' => [],
    ];
    // Group address parts to address related and city with postal code.
    foreach ($devided_lines as $line) {
      if (in_array($line, ['locality', 'administrativeArea', 'postalCode'])) {
        $reformatted_lines['city'][] = '%' . $line;
      }
      else {
        $reformatted_lines['address'][] = '%' . $line;
      }
    }

    $address = implode(" ", $reformatted_lines['address']);
    $city = implode(" ", $reformatted_lines['city']);
    $string = $address . "\n" . $city;

    return parent::replacePlaceholders($string, $replacements);
  }

}
