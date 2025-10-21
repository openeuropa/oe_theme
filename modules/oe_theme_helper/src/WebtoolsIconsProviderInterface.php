<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper;

/**
 * Interface for the WebtoolsIconsProvider service.
 */
interface WebtoolsIconsProviderInterface {

  /**
   * Get allowed icon values for a given icons set.
   *
   * @param array $icons_sets
   *   The icons sets to retrieve allowed values for.
   * @param array $tags
   *   The tags to filter the icons from the given sets.
   *
   * @return array
   *   An associative array of allowed icon values, where keys are icon names.
   */
  public function getAllowedIconValues(array $icons_sets = ['icons'], array $tags = []): array;

  /**
   * Get the icon family for a given icon name.
   *
   * @param string $icon_name
   *   The name of the icon to retrieve the family for.
   *
   * @return string|null
   *   The icon family name if found, or NULL if not found.
   */
  public function getIconFamily(string $icon_name): ?string;

  /**
   * Get webtools icons.
   *
   * @return array
   *   An associative array containing the icons, flags, and networks.
   */
  public function getWebtoolsIcons(): array;

  /**
   * Get the cache tags for the webtools icons.
   *
   * @return array
   *   The cache tags for the webtools icons.
   */
  public function getCacheTags(): array;

}
