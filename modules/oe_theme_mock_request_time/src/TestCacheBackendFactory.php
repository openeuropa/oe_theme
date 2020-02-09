<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_mock_request_time;

use Drupal\Core\Cache\CacheTagsChecksumInterface;
use Drupal\Core\Cache\DatabaseBackendFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Site\Settings;

/**
 * Test cache backend factory, with a configurable request time.
 */
class TestCacheBackendFactory extends DatabaseBackendFactory {

  /**
   * Mock request time manager service.
   *
   * @var \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface
   */
  protected $requestTimeManager;

  /**
   * Constructs the TestCacheBackendFactory object.
   *
   * @param \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface $request_time_manager
   *   Mock request time manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Cache\CacheTagsChecksumInterface $checksum_provider
   *   The cache tags checksum provider.
   * @param \Drupal\Core\Site\Settings $settings
   *   (optional) The site settings.
   *
   * @throws \BadMethodCallException
   */
  public function __construct(MockRequestTimeManagerInterface $request_time_manager, Connection $connection, CacheTagsChecksumInterface $checksum_provider, Settings $settings = NULL) {
    parent::__construct($connection, $checksum_provider, $settings);
    $this->requestTimeManager = $request_time_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    $max_rows = $this->getMaxRowsForBin($bin);
    return new TestCacheBackend($this->requestTimeManager, $this->connection, $this->checksumProvider, $bin, $max_rows);
  }

}
