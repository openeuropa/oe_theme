<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use CommerceGuys\Addressing\Locale;
use Drupal\Core\Render\Element;
use Drupal\address\AddressInterface;
use Drupal\address\Plugin\Field\FieldFormatter\AddressDefaultFormatter;

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
   *
   * @return string
   *   The processed string.
   */
  protected static function updateFormat(&$string) {
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

    return $string;
  }

  /**
   * Inserts the rendered elements into the format string.
   *
   * @param string $content
   *   The rendered element.
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element.
   *
   * @return string
   *   The new rendered element.
   */
  public static function postRender($content, array $element) {
    /** @var \CommerceGuys\Addressing\AddressFormat\AddressFormat $address_format */
    $address_format = $element['#address_format'];
    $locale = $element['#locale'];
    // Add the country to the bottom or the top of the format string,
    // depending on whether the format is minor-to-major or major-to-minor.
    if (Locale::matchCandidates($address_format->getLocale(), $locale)) {
      $format_string = $address_format->getLocalFormat();
      self::updateFormat($format_string);
      $format_string = '%country' . "\n" . $format_string;
    }
    else {
      $format_string = $address_format->getFormat();
      self::updateFormat($format_string);
      $format_string = $format_string . "\n" . '%country';
    }

    $replacements = [];
    foreach (Element::getVisibleChildren($element) as $key) {
      $child = $element[$key];
      if (isset($child['#placeholder'])) {
        $replacements[$child['#placeholder']] = $child['#value'] ? $child['#markup'] : '';
      }
    }
    $content = self::replacePlaceholders($format_string, $replacements);
    $content = nl2br($content, FALSE);

    return $content;
  }

}
