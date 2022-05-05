<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/**
 * Verifies if a URL is considered external or internal.
 */
class ExternalLinks implements ExternalLinksInterface {

  /**
   * {@inheritdoc}
   */
  public function isExternalLink($url): bool {
    if ($url instanceof Url) {
      $external = $url->isExternal();
      $path = UrlHelper::parse($url->toString())['path'];
    }
    else {
      $external = UrlHelper::isExternal($url);
      $path = UrlHelper::parse($url)['path'];
    }
    if (!$external) {
      return $external;
    }

    // If it's external link, make sure its domain is not considered internal.
    $internal_domains = $this->internalDomains();
    foreach ($internal_domains as $internal_domain) {
      if (strpos($path, $internal_domain)) {
        $external = FALSE;
        break;
      }
    }

    return $external;
  }

  /**
   * Defines a list of domain considered internal.
   *
   * @return array|null
   *   The list of internal domains or NULL if there is none.
   */
  protected function internalDomains(): ?array {
    return [
      'europa.eu',
    ];
  }

}
