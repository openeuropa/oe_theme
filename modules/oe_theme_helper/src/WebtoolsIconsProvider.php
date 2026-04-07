<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
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

  public const ICONS_URL = 'https://webtools.europa.eu/rest/wshape';

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
  public function getAllowedIconValues(array $icons_sets = ['icons'], array $tags = []): array {
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
        $this->getAllowedFlags($icons, $allowed_values, $tags);
        continue;
      }

      $this->getAllowedIconsFromSet($icons, $icons_set, $allowed_values, $single_set, $tags);
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
   * @param string|null $icons_family
   *   The icons family.
   *
   * @return array
   *   The array of icons.
   */
  protected function downloadWebtoolsIcons(?string $icons_family = NULL): array {
    $icons_url = Settings::get('webtools_wshape.url') ?: static::ICONS_URL;
    try {
      /** @var \Drupal\Core\Url $url */
      $url = Url::fromUri($icons_url);

      if (!empty($icons_family)) {
        $url->setOption('query', ['family' => $icons_family]);
      }

      $json_response = $this->httpClient->get($url->setAbsolute()->toString());
      $icons = json_decode(
        json: $json_response->getBody()->getContents(),
        associative: TRUE,
        flags: JSON_THROW_ON_ERROR
      );
    }
    catch (RequestException | \InvalidArgumentException | ConnectException $e) {
      $this->loggerFactory->get('oe_theme')->error('HTTP request to @url failed with error: @error.', [
        '@url' => $icons_url,
        '@error' => $e->getMessage(),
      ]);
      return [];
    }
    catch (\JsonException $e) {
      $this->loggerFactory->get('oe_theme')->error('Failed to decode JSON response from @url with error: @error.', [
        '@url' => $icons_url,
        '@error' => $e->getMessage(),
      ]);
      return [];
    }

    return $icons;
  }

  /**
   * Get the allowed flags.
   *
   * @param array $icons
   *   The flags icons set.
   * @param array $allowed_values
   *   The allowed values array to be updated.
   * @param array $tags
   *   The tags to filter the icons by, if needed.
   */
  protected function getAllowedFlags(array $icons, array &$allowed_values, array $tags = []): void {
    if (!empty($tags)) {
      foreach ($tags as $tag) {
        foreach ($icons['flags']['categories'] as $category => $country_codes) {
          foreach ($country_codes as $country_code) {
            if (!isset($icons['flags']['all'][$country_code]['default']['tags']) || !in_array($tag, $icons['flags']['all'][$country_code]['default']['tags'])) {
              continue;
            }
            // Ensure the category name is readable.
            $category = str_replace('_', ' ', strtoupper($category));
            $country_name = $icons['flags']['all'][$country_code]['default']['title'] ?? strtoupper($country_code);
            if ($country_name) {
              $allowed_values[$category][$country_code] = $country_name;
            }
          }
        }
      }
      return;
    }
    foreach ($icons['flags']['categories'] as $category => $country_codes) {
      foreach ($country_codes as $country_code) {
        // Ensure the category name is readable.
        $category = str_replace('_', ' ', strtoupper($category));
        $country_name = $icons['flags']['all'][$country_code]['default']['title'] ?? strtoupper($country_code);
        if ($country_name) {
          $allowed_values[$category][$country_code] = $country_name;
        }
      }
    }
  }

  /**
   * Get the allowed flags.
   *
   * @param array $icons
   *   The icons array.
   * @param string $icons_set
   *   The icons set.
   * @param array $allowed_values
   *   The allowed values array to be updated.
   * @param bool $single_set
   *   Whether a flat array is needed or not.
   * @param array $tags
   *   The tags to filter the icons by, if needed.
   */
  protected function getAllowedIconsFromSet(array $icons, string $icons_set, array &$allowed_values, bool $single_set = FALSE, array $tags = []): void {
    if (!empty($tags)) {
      foreach ($tags as $tag) {
        foreach ($icons[$icons_set]['all'] as $icon => $info) {
          if (!isset($info['default']['tags']) || !in_array($tag, $info['default']['tags'])) {
            continue;
          }
          $title = $icons[$icons_set]['all'][$icon]['default']['title'] ?? ucfirst($icon);

          if ($single_set) {
            $allowed_values[$icon] = $title;
          }
          else {
            $allowed_values[strtoupper($icons_set)][$icon] = $title;
          }
        }
      }
      return;
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

}
