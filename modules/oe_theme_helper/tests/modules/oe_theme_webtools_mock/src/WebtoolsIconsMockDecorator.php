<?php

declare(strict_types=1);

namespace Drupal\oe_theme_webtools_mock;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\oe_theme_helper\WebtoolsIconsProvider;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use GuzzleHttp\ClientInterface;

/**
 * The decorated WebtoolsIconsProvider service.
 */
class WebtoolsIconsMockDecorator extends WebtoolsIconsProvider {

  /**
   * Constructs a WebtoolsIconsMockDecorator object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface $timeBasedCacheTagGenerator
   *   The time-based cache tag generator.
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extensionPathResolver
   *   The extension path resolver.
   */
  public function __construct(
    protected ClientInterface $httpClient,
    protected CacheBackendInterface $cache,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected TimeInterface $time,
    protected TimeBasedCacheTagGeneratorInterface $timeBasedCacheTagGenerator,
    protected ExtensionPathResolver $extensionPathResolver,
  ) {
    parent::__construct($httpClient, $cache, $loggerFactory, $time, $timeBasedCacheTagGenerator);
  }

  /**
   * {@inheritdoc}
   */
  protected function downloadWebtoolsIcons(): array {
    $path = $this->extensionPathResolver->getPath('module', 'oe_theme_webtools_mock');
    $json_string = file_get_contents($path . '/assets/icons.json');
    return Json::decode($json_string);
  }

}
