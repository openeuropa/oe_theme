<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides webtools icons for fields.
 */
class WebtoolsIconsProvider implements WebtoolsIconsProviderInterface {

  public const CACHE_ID = 'oe_theme_webtools_icons';

  public const CACHE_TAG = 'oe_theme_webtools_icons';

  public const ICONS_URL = 'https://webtools.europa.eu/icons.json';

  /**
   * Constructs a WebtoolsIconsProvider object.
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
   */
  public function __construct(
    protected ClientInterface $httpClient,
    protected CacheBackendInterface $cache,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected TimeInterface $time,
    protected TimeBasedCacheTagGeneratorInterface $timeBasedCacheTagGenerator,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getAllowedIconValues(array $icons_sets = ['icons']): array {
    $icons = $this->getWebtoolsIcons();
    $allowed_values = [];

    // Determine if we only need a flat array.
    $single_set = count($icons_sets) === 1;

    foreach ($icons_sets as $icons_set) {
      if (empty($icons[$icons_set]['name'])) {
        continue;
      }

      // Special handling for flags to ensure categorised structure.
      if ($icons_set === 'flags') {
        foreach ($icons[$icons_set]['categories'] as $category => $country_codes) {
          // Ensure the category name is readable.
          $category = str_replace('_', ' ', strtoupper($category));

          foreach ($country_codes as $country_code) {
            $country_name = $icons[$icons_set]['all'][$country_code]['default']['title'] ?? strtoupper($country_code);
            if ($country_name) {
              $allowed_values[$category][$country_code] = $country_name;
            }
          }
        }

        continue;
      }

      foreach ($icons[$icons_set]['name'] as $icon_name) {
        $title = $icons[$icons_set]['all'][$icon_name]['default']['title'] ?? ucfirst($icon_name);

        if ($single_set) {
          $allowed_values[$icon_name] = $title;
        }
        else {
          $allowed_values[strtoupper($icons_set)][$icon_name] = $title;
        }
      }
    }

    return $allowed_values;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconFamily(string $icon_name): ?string {
    $icons = $this->getWebtoolsIcons();

    foreach ($icons as $category => $icon_set) {
      if (empty($icon_set['name'])) {
        continue;
      }

      if (in_array($icon_name, $icon_set['name'], TRUE)) {
        if ($category === 'icons') {
          // For generic icons we return empty string.
          return '';
        }
        return $category;
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getWebtoolsIcons(): array {
    if ($cache = $this->cache->get(static::CACHE_ID)) {
      return $cache->data;
    }

    $icons = $this->downloadWebtoolsIcons();

    $this->cache->set(
      static::CACHE_ID,
      [
        'flags' => $icons['flags'] ?? [],
        'icons' => $icons['icons'] ?? [],
        'networks' => $icons['networks'] ?? [],
        'ecl' => $icons['ecl'] ?? [],
      ],
      CacheBackendInterface::CACHE_PERMANENT,
      $this->getCacheTags()
    );

    return $icons;
  }

  /**
   * {@inheritdoc}
   */
  public function getSvgPath(string $icon_name): ?string {
    $icons = $this->getWebtoolsIcons();
    foreach ($icons as $icon_set) {
      if (empty($icon_set['name'])) {
        continue;
      }

      if (in_array($icon_name, $icon_set['name'], TRUE) && !empty($icon_set['path'])) {
        return str_replace('{name}', $icon_name, $icon_set['path']);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getInfoValues(string $icon_name, ?string $language_name = 'default'): array {
    $icons = $this->getWebtoolsIcons();
    foreach ($icons as $icon_set) {
      if (empty($icon_set['name'])) {
        continue;
      }

      if (in_array($icon_name, $icon_set['name'], TRUE) && !empty($icon_set['all'][$icon_name])) {
        if (!empty($icon_set['all'][$icon_name]['languages'][$language_name])) {
          return $icon_set['all'][$icon_name]['languages'][$language_name];
        }
        else {
          return $icon_set['all'][$icon_name]['default'] ?? [];
        }
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    if ($cache = $this->cache->get(static::CACHE_ID)) {
      // Return the same oe_timed_cache tags as the cache item.
      return $cache->tags;
    }

    // We need to generate tags based on the current time.
    $current_time = $this->time->getCurrentTime();
    $today1 = new DrupalDateTime();
    $today1->setTimestamp($current_time);
    $today1->setTimezone(new \DateTimeZone('UTC'));
    // We expect the cache to expire at 1am UTC.
    $today1->setTime(1, 0);

    $today8 = new DrupalDateTime();
    $today8->setTimestamp($current_time);
    $today8->setTimezone(new \DateTimeZone('UTC'));
    // We expect the cache to expire at 8am UTC.
    $today8->setTime(8, 0);

    if ($current_time < $today1->getTimestamp()) {
      $expiry = $today1;
    }
    elseif ($current_time < $today8->getTimestamp()) {
      $expiry = $today8;
    }
    else {
      // After 8am, expire next day at 1am.
      $expiry = $today1->modify('+1 day');
    }

    $tags = $this->timeBasedCacheTagGenerator->generateTags($expiry->getPhpDateTime());
    $tags[] = static::CACHE_TAG;

    return $tags;
  }

  /**
   * Downloads the webtools icons from the JSON file.
   *
   * @return array
   *   The array of icons.
   */
  protected function downloadWebtoolsIcons(): array {
    try {
      $json_response = $this->httpClient->get(static::ICONS_URL);
      $icons = json_decode(
        json: $json_response->getBody()->getContents(),
        associative: TRUE,
        flags: JSON_THROW_ON_ERROR
      );
    }
    catch (RequestException | ConnectException $e) {
      $this->loggerFactory->get('oe_theme')->error('HTTP request to @url failed with error: @error.', [
        '@url' => static::ICONS_URL,
        '@error' => $e->getMessage(),
      ]);
      return [];
    }
    catch (\JsonException $e) {
      $this->loggerFactory->get('oe_theme')->error('Failed to decode JSON response from @url with error: @error.', [
        '@url' => static::ICONS_URL,
        '@error' => $e->getMessage(),
      ]);
      return [];
    }

    return $icons;
  }

}
