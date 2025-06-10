<?php

declare(strict_types=1);

namespace Drupal\oe_theme\Traits;

/**
 * Provides a trait for mapping ISO country codes to country names.
 *
 * This trait can be used in any class that needs to convert ISO country codes
 * to their corresponding country names.
 */
trait CountryMappingTrait {

  /**
   * Returns the country name for a given ISO code.
   *
   * @param string $iso_code
   *   The ISO code of the country (e.g., 'at' for Austria).
   *
   * @return string|null
   *   The name of the country, or NULL if the ISO code is not recognized.
   */
  public function getCountryName(string $iso_code): ?string {
    $countries = [
      'at' => 'Austria',
      'be' => 'Belgium',
      'bg' => 'Bulgaria',
      'hr' => 'Croatia',
      'cy' => 'Cyprus',
      'cz' => 'Czechia',
      'dk' => 'Denmark',
      'ee' => 'Estonia',
      'eu' => 'EU',
      'fi' => 'Finland',
      'fr' => 'France',
      'de' => 'Germany',
      'el' => 'Greece',
      'hu' => 'Hungary',
      'ie' => 'Ireland',
      'it' => 'Italy',
      'lv' => 'Latvia',
      'lt' => 'Lithuania',
      'lu' => 'Luxembourg',
      'mt' => 'Malta',
      'nl' => 'Netherlands',
      'pl' => 'Poland',
      'pt' => 'Portugal',
      'ro' => 'Romania',
      'sk' => 'Slovakia',
      'si' => 'Slovenia',
      'es' => 'Spain',
      'se' => 'Sweden',
      // EFTA countries.
      'ch' => 'Switzerland',
      'is' => 'Iceland',
      'li' => 'Liechtenstein',
      'no' => 'Norway',
      // Candidate countries.
      'al' => 'Albania',
      'ba' => 'Bosnia and Herzegovina',
      'ge' => 'Georgia',
      'md' => 'Moldova',
      'me' => 'Montenegro',
      'mk' => 'North Macedonia',
      'rs' => 'Serbia',
      'tr' => 'Turkey',
      'ua' => 'Ukraine',
      // Other countries.
      'am' => 'Armenia',
      'il' => 'Israel',
      'uk' => 'United Kingdom',
    ];

    if (!array_key_exists($iso_code, $countries)) {
      return NULL;
    }

    return $countries[$iso_code];
  }

  /**
   * Returns the country ISO code for a given name.
   *
   * @param string $country_name
   *   The name of the country (e.g., 'austria' for 'at').
   *
   * @return string|null
   *   The ISO code of the country, or NULL if the name is not recognized.
   */
  public function getCountryCode(string $country_name): ?string {
    $countries = [
      'austria' => 'at',
      'belgium' => 'be',
      'bulgaria' => 'bg',
      'croatia' => 'hr',
      'cyprus' => 'cy',
      'czechia' => 'cz',
      'denmark' => 'dk',
      'estonia' => 'ee',
      'eu' => 'eu',
      'finland' => 'fi',
      'france' => 'fr',
      'germany' => 'de',
      'greece' => 'el',
      'hungary' => 'hu',
      'ireland' => 'ie',
      'italy' => 'it',
      'latvia' => 'lv',
      'lithuania' => 'lt',
      'luxembourg' => 'lu',
      'malta' => 'mt',
      'netherlands' => 'nl',
      'poland' => 'pl',
      'portugal' => 'pt',
      'romania' => 'ro',
      'slovakia' => 'sk',
      'slovenia' => 'si',
      'spain' => 'es',
      'sweden' => 'se',
      // EFTA countries.
      'switzerland' => 'ch',
      'iceland' => 'is',
      'liechtenstein' => 'li',
      'norway' => 'no',
      // Candidate countries.
      'albania' => 'al',
      'bosnia-and-herzegovina' => 'ba',
      'georgia' => 'ge',
      'moldova' => 'md',
      'montenegro' => 'me',
      'north-macedonia' => 'mk',
      'serbia' => 'rs',
      'turkey' => 'tr',
      'ukraine' => 'ua',
      // Other countries.
      'armenia' => 'am',
      'israel' => 'il',
      'united-kingdom' => 'uk',
    ];

    if (!array_key_exists($country_name, $countries)) {
      return NULL;
    }

    return $countries[$country_name];
  }

}
