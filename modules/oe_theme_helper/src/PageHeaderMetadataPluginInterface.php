<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

/**
 * Interface definition for page header metadata plugins.
 */
interface PageHeaderMetadataPluginInterface {

  /**
   * Determines if the plugin applies to the current rendering request.
   *
   * For example, a plugin might apply only if the current route is a canonical
   * entity route.
   *
   * @return bool
   *   True if the plugin applies, false otherwise.
   */
  public function applies(): bool;

  /**
   * Retrieves metadata for the page header.
   *
   * Only invoked upon a positive result of the self::applies() method.
   *
   * @return array
   *   The page header metadata if applicable, null otherwise.
   */
  public function getMetadata(): array;

}
