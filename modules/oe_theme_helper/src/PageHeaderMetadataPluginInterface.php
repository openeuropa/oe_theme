<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

/**
 * Interface definition for page header metadata plugins.
 */
interface PageHeaderMetadataPluginInterface {

  /**
   * Retrieves metadata.
   *
   * @return array|null
   *   The page header metadata if applicable, null otherwise.
   */
  public function getMetadata(): ?array;

}
