<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper;

use Drupal\Core\Url;

/**
 * Interface for the external links service.
 */
interface ExternalLinksInterface {

  /**
   * Checks whether a link is considered external.
   *
   * @param \Drupal\Core\Url|string $url
   *   The url object.
   *
   * @return bool
   *   TRUE if the link is external, FALSE otherwise.
   */
  public function isExternalLink(Url $url): bool;

}
