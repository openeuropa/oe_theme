<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\PageHeaderMetadata;

use Drupal\oe_theme_helper\PageHeaderMetadataPluginBase;

/**
 * Defines the default page header metadata plugin.
 *
 * @PageHeaderMetadata(
 *   id = "default",
 *   label = @Translation("Default metadata extractor"),
 *   weight = 100
 * )
 */
class DefaultMetadata extends PageHeaderMetadataPluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    // No additional metadata: the block will show the default page title.
    $metadata = [];

    return $metadata;
  }

}
