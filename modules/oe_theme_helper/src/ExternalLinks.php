<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;

/**
 * Verifies if a URL is considered external or internal.
 */
class ExternalLinks implements ExternalLinksInterface {

  /**
   * The internal domain regex.
   *
   * @var string
   */
  protected string $internalDomainExpression;

  /**
   * Constructs an ExternalLinks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->internalDomainExpression = $config_factory->get('oe_theme_helper.internal_domains')->get('internal_domain') ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function isExternalLink($url = NULL): bool {
    // If no value is provided or the value si not a proper link, we'll
    // return FALSE as it can't be evaluated.
    if (!$url) {
      return FALSE;
    }
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

    // If it's an external link, make sure its domain is not internal.
    if (!$this->internalDomainExpression) {
      return $external;
    }
    return !preg_match_all($this->internalDomainExpression, $path);
  }

}
