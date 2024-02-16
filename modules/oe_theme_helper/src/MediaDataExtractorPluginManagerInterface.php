<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper;

/**
 * Interface for media data extractor plugin managers.
 *
 * @internal
 */
interface MediaDataExtractorPluginManagerInterface {

  /**
   * Create a plugin instance given a media.
   *
   * @param string $bundle
   *   The bundle ID.
   * @param array $configuration
   *   The plugin configuration.
   *
   * @return \Drupal\oe_theme_helper\MediaDataExtractorInterface
   *   The media data extractor plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown when no plugin is specified for the bundle.
   */
  public function createInstanceByMediaBundle(string $bundle, array $configuration = []): MediaDataExtractorInterface;

}
