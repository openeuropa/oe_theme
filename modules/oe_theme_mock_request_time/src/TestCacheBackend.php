<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_mock_request_time;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheTagsChecksumInterface;
use Drupal\Core\Cache\DatabaseBackend;
use Drupal\Core\Database\Connection;

/**
 * Test cache backend, with a configurable request time.
 *
 * We have no other way than reusing the actual method codes since the parent
 * class uses REQUEST_TIME constant, which is set at bootstrap time.
 */
class TestCacheBackend extends DatabaseBackend {

  /**
   * Mock request time manager service.
   *
   * @var \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface
   */
  protected $requestTimeManager;

  /**
   * Constructs a TestCacheBackend object.
   *
   * @param \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface $request_time_manager
   *   Mock request time manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Cache\CacheTagsChecksumInterface $checksum_provider
   *   The cache tags checksum provider.
   * @param string $bin
   *   The cache bin for which the object is created.
   * @param int $max_rows
   *   (optional) The maximum number of rows that are allowed in this cache bin
   *   table.
   */
  public function __construct(MockRequestTimeManagerInterface $request_time_manager, Connection $connection, CacheTagsChecksumInterface $checksum_provider, $bin, $max_rows = NULL) {
    parent::__construct($connection, $checksum_provider, $bin, $max_rows);
    $this->requestTimeManager = $request_time_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateMultiple(array $cids) {
    $cids = array_values(array_map([$this, 'normalizeCid'], $cids));
    try {
      // Update in chunks when a large array is passed.
      foreach (array_chunk($cids, 1000) as $cids_chunk) {
        $this->connection->update($this->bin)
          ->fields(['expire' => $this->requestTimeManager->get() - 1])
          ->condition('cid', $cids_chunk, 'IN')
          ->execute();
      }
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateAll() {
    try {
      $this->connection->update($this->bin)
        ->fields(['expire' => $this->requestTimeManager->get() - 1])
        ->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    try {
      // Bounded size cache bin, using FIFO.
      if ($this->maxRows !== static::MAXIMUM_NONE) {
        $first_invalid_create_time = $this->connection->select($this->bin)
          ->fields($this->bin, ['created'])
          ->orderBy("{$this->bin}.created", 'DESC')
          ->range($this->maxRows, $this->maxRows + 1)
          ->execute()
          ->fetchField();

        if ($first_invalid_create_time) {
          $this->connection->delete($this->bin)
            ->condition('created', $first_invalid_create_time, '<=')
            ->execute();
        }
      }

      $this->connection->delete($this->bin)
        ->condition('expire', Cache::PERMANENT, '<>')
        ->condition('expire', $this->requestTimeManager->get(), '<')
        ->execute();
    }
    catch (\Exception $e) {
      // If the table does not exist, it surely does not have garbage in it.
      // If the table exists, the next garbage collection will clean up.
      // There is nothing to do.
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareItem($cache, $allow_invalid) {
    if (!isset($cache->data)) {
      return FALSE;
    }

    $cache->tags = $cache->tags ? explode(' ', $cache->tags) : [];

    // Check expire time.
    $cache->valid = $cache->expire == Cache::PERMANENT || $cache->expire >= $this->requestTimeManager->get();

    // Check if invalidateTags() has been called with any of the items's tags.
    if (!$this->checksumProvider->isValid($cache->checksum, $cache->tags)) {
      $cache->valid = FALSE;
    }

    if (!$allow_invalid && !$cache->valid) {
      return FALSE;
    }

    // Unserialize and return the cached data.
    if ($cache->serialized) {
      $cache->data = unserialize($cache->data);
    }

    return $cache;
  }

}
