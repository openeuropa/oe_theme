<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme_helper\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\oe_theme_helper\WebtoolsIconsProvider;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Tests webtools icons allowed values function.
 *
 * @group batch2
 */
class IconsAllowedValuesTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime',
    'datetime_testing',
    'oe_time_caching',
    'oe_theme_webtools_mock',
  ];

  /**
   * Tests that the allowed values for icons are returned correctly.
   */
  public function testIconsAllowedValues(): void {
    $expected = $this->getExpectedIconValues();
    $cases = [
      [
        'sets' => ['icons'],
        'tags' => ['unordered-list', 'no-tag'],
      ],
      [
        'sets' => ['flags'],
        'tags' => [],
      ],
      [
        'sets' => ['networks'],
        'tags' => [],
      ],
      [
        'sets' => ['icons', 'networks'],
        'tags' => [],
      ],
    ];
    foreach ($cases as $case) {
      $allowed_values = $this->container->get('oe_theme_helper.webtools_icons_provider')->getAllowedIconValues($case['sets']);
      if (!empty($case['tags'])) {
        foreach ($case['tags'] as $tag) {
          $allowed_values = $tag === 'no-tag' ? $this->container->get('oe_theme_helper.webtools_icons_provider')->getAllowedIconValues($case['sets']) : $this->container->get('oe_theme_helper.webtools_icons_provider')->getAllowedIconValues($case['sets'], [$tag]);
          $expected_values = $expected[$case['sets'][0]][$tag];
          $this->assertSame($expected_values, $allowed_values, sprintf('Allowed values for icons set "%s" are not as expected.', implode(', ', $case['sets'])));
        }
        continue;
      }
      // If the case is mixed we need to adapt the returned array to look into
      // the category values one level lower.
      if (count($case['sets']) > 1) {
        foreach ($case['sets'] as $category) {
          $expected_values = $expected[$category]['no-tag'] ?? $expected[$category];
          $this->assertSame($expected_values, $allowed_values[strtoupper($category)], sprintf('Allowed values for icons set "%s" are not as expected.', implode(', ', $case['sets'])));
        }
        continue;
      }
      $expected_values = $expected[$case['sets'][0]]['no-tag'] ?? $expected[$case['sets'][0]];
      $this->assertSame($expected_values, $allowed_values, sprintf('Allowed values for icons set "%s" are not as expected.', implode(', ', $case['sets'])));
    }
  }

  /**
   * Tests that cache tags are returned correctly.
   */
  public function testWebtoolsIconsProviderCacheTags(): void {
    $static_time = new DrupalDateTime('2025-05-05 15:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->container->get('datetime.time')->freezeTime();
    $this->container->get('datetime.time')->setTime($static_time->getTimestamp());

    $cache = $this->container->get('cache.default')->get(WebtoolsIconsProvider::CACHE_ID);
    if ($cache) {
      // Get rid of the cached data to ensure we test the cache tags.
      $this->container->get('cache.default')->delete(WebtoolsIconsProvider::CACHE_ID);
    }

    $this->container->get('oe_theme_helper.webtools_icons_provider')->getWebtoolsIcons();
    $cache = $this->container->get('cache.default')->get(WebtoolsIconsProvider::CACHE_ID);

    $expected = [
      0 => 'oe_theme_webtools_icons',
      1 => 'oe_time_caching_date:2025',
      2 => 'oe_time_caching_date:2025-05',
      3 => 'oe_time_caching_date:2025-05-06',
      4 => 'oe_time_caching_date:2025-05-06-01',
    ];
    $this->assertSame($expected, $cache->tags, 'Cache tags for webtools icons are not as expected.');
    $this->container->get('datetime.time')->unfreezeTime();
    $this->container->get('datetime.time')->resetTime();
  }

  /**
   * Get the expected icon values for testing.
   *
   * @return array
   *   An associative array containing the expected icon values.
   */
  protected function getExpectedIconValues(): array {
    return [
      'icons' => [
        'unordered-list' => [
          'arrow-right-bold' => 'Arrow right bold',
          'check-bold' => 'Check bold',
          'close-bold' => 'Close bold',
        ],
        'no-tag' => [
          'arrow-down' => 'Arrow down',
          'arrow-downup' => 'Arrow-downup',
          'arrow-left' => 'Arrow left',
          'arrow-right-bold' => 'Arrow right bold',
          'arrow-right' => 'Arrow-right',
          'arrow-up' => 'Arrow-up',
          'audio' => 'Audio',
          'book' => 'Book',
          'brochure' => 'Brochure',
          'budget' => 'Budget',
          'calendar' => 'Calendar',
          'camera' => 'Camera',
          'chain' => 'Chain',
          'check-bold' => 'Check bold',
          'check-filled' => 'Check filled',
          'check' => 'Check',
          'clock-filled' => 'Clock filled',
          'clock' => 'Clock',
          'close-bold' => 'Close bold',
          'close-filled' => 'Close filled',
          'close-outline' => 'Close outline',
          'close' => 'Close',
          'copy' => 'Copy',
          'corner-arrow-down' => 'Corner-arrow-down',
          'corner-arrow-left' => 'Corner-arrow-left',
          'corner-arrow-right' => 'Corner-arrow-right',
          'corner-arrow-up' => 'Corner-arrow-up',
          'corner-arrow' => 'Corner arrow',
          'crosshair' => 'Crosshair',
          'data' => 'Data',
          'digital' => 'Digital',
          'document' => 'Document',
          'download' => 'Download',
          'edit' => 'Edit',
          'email' => 'Email',
          'energy' => 'Energy',
          'error-filled' => 'Error-filled',
          'error-outline' => 'Error outline',
          'error' => 'Error',
          'euro' => 'Euro',
          'event' => 'Event',
          'external-events' => 'External events',
          'external' => 'External',
          'eye' => 'Eye',
          'faq' => 'Faq',
          'feedback' => 'Feedback',
          'file-blank' => 'File blank',
          'file' => 'File',
          'folder' => 'Folder',
          'fullscreen' => 'Fullscreen',
          'global' => 'Global',
          'growth' => 'Growth',
          'hamburger' => 'Hamburger',
          'image' => 'Image',
          'infographic' => 'Infographic',
          'information-filled' => 'Information-filled',
          'information-outline' => 'Information outline',
          'information-round' => 'Information round',
          'information' => 'Information',
          'laco-filled' => 'Laco filled',
          'laco' => 'Laco',
          'list' => 'List',
          'livestreaming' => 'Livestreaming',
          'location-filled' => 'Location filled',
          'location' => 'Location',
          'log-in-outline' => 'Log in outline',
          'log-in' => 'Log in',
          'logged-in' => 'Logged in',
          'minus-outline' => 'Minus outline',
          'minus' => 'Minus',
          'move' => 'Move',
          'multiple-files' => 'Multiple files',
          'notification-active' => 'Notification active',
          'notification' => 'Notification',
          'organigram' => 'Organigram',
          'package' => 'Package',
          'pause-filled' => 'Pause filled',
          'pause-outline' => 'Pause outline',
          'pause' => 'Pause',
          'play-filled' => 'Play filled',
          'play-outline' => 'Play outline',
          'play' => 'Play',
          'plus-filled' => 'Plus filled',
          'plus-outline' => 'Plus outline',
          'plus' => 'Plus',
          'presentation' => 'Presentation',
          'print' => 'Print',
          'question' => 'Question',
          'refresh' => 'Refresh',
          'regulation' => 'Regulation',
          'rss' => 'Rss',
          'search' => 'Search',
          'settings' => 'Settings',
          'share-filled' => 'Share filled',
          'share' => 'Share',
          'shopping-bag' => 'Shopping bag',
          'solid-arrow-down' => 'Solid-arrow-down',
          'solid-arrow-left' => 'Solid-arrow-left',
          'solid-arrow-right' => 'Solid-arrow-right',
          'solid-arrow-up' => 'Solid-arrow-up',
          'solid-arrow' => 'Solid arrow',
          'sort-carets' => 'Sort carets',
          'spinner' => 'Spinner',
          'spreadsheet' => 'Spreadsheet',
          'star-filled' => 'Star filled',
          'star-outline' => 'Star outline',
          'success-filled' => 'Success-filled',
          'success-outline' => 'Success-outline',
          'tag' => 'Tag',
          'trash' => 'Trash',
          'upload' => 'Upload',
          'warning-filled' => 'Warning-filled',
          'warning-outline' => 'Warning outline',
          'warning-round' => 'Warning round',
          'warning' => 'Warning',
        ],
      ],
      'flags' => [
        'EU27' => [
          'de' => 'Germany',
          'el' => 'Greece',
          'fr' => 'France',
          'pl' => 'Poland',
          'si' => 'Slovenia',
          'be' => 'Belgium',
          'bg' => 'Bulgaria',
          'cz' => 'Czechia',
          'dk' => 'Denmark',
          'ee' => 'Estonia',
          'ie' => 'Ireland',
          'es' => 'Spain',
          'hr' => 'Croatia',
          'it' => 'Italy',
          'cy' => 'Cyprus',
          'lv' => 'Latvia',
          'lt' => 'Lithuania',
          'lu' => 'Luxembourg',
          'hu' => 'Hungary',
          'mt' => 'Malta',
          'nl' => 'Netherlands',
          'at' => 'Austria',
          'pt' => 'Portugal',
          'ro' => 'Romania',
          'sk' => 'Slovakia',
          'fi' => 'Finland',
          'se' => 'Sweden',
          'eu' => 'European Union',
        ],
        'EFTA' => [
          'is' => 'Iceland',
          'li' => 'Liechtenstein',
          'no' => 'Norway',
          'ch' => 'Switzerland',
        ],
        'CANDIDATE COUNTRIES' => [
          'al' => 'Albania',
          'ba' => 'Bosnia and Herzegovina',
          'ge' => 'Georgia',
          'md' => 'Moldova',
          'me' => 'Montenegro',
          'mk' => 'North Macedonia',
          'rs' => 'Serbia',
          'tr' => 'Türkiye',
          'ua' => 'Ukraine',
        ],
        'OTHER' => [
          'am' => 'Armenia',
          'il' => 'Israel',
          'uk' => 'United Kingdom',
        ],
      ],
      'networks' => [
        'blog' => 'Blog',
        'blogger' => 'Blogger',
        'bluesky' => 'Bluesky',
        'chain' => 'Chain',
        'digg' => 'Digg',
        'email' => 'Email',
        'facebook' => 'Facebook',
        'flickr' => 'Flickr',
        'foursquare' => 'Foursquare',
        'gmail' => 'Gmail',
        'instagram' => 'Instagram',
        'linkedin' => 'Linkedin',
        'mastodon' => 'Mastodon',
        'messenger' => 'Messenger',
        'netvibes' => 'Netvibes',
        'pinterest' => 'Pinterest',
        'pocket' => 'Pocket',
        'printfriendly' => 'Printfriendly',
        'qzone' => 'Qzone',
        'reddit' => 'Reddit',
        'rss' => 'Rss',
        'share' => 'Share',
        'skype' => 'Skype',
        'sms' => 'Sms',
        'spotify' => 'Spotify',
        'telegram' => 'Telegram',
        'threads' => 'Threads',
        'tumblr' => 'Tumblr',
        'twitter' => 'Twitter',
        'typepad' => 'Typepad',
        'viadeo' => 'Viadeo',
        'viber' => 'Viber',
        'vimeo' => 'Vimeo',
        'weibo' => 'Weibo',
        'whatsapp' => 'Whatsapp',
        'x' => 'X',
        'yahoomail' => 'Yahoomail',
        'yammer' => 'Yammer',
        'youtube' => 'Youtube',
      ],
    ];
  }

}
