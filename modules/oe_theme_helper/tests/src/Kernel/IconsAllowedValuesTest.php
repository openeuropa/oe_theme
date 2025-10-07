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
      ['icons'],
      ['flags'],
      ['networks'],
      ['icons', 'networks'],
    ];
    foreach ($cases as $case) {
      $allowed_values = $this->container->get('oe_theme_helper.webtools_icons_provider')->getAllowedIconValues($case);
      // If the case is mixed we need to adapt the returned array to look into
      // the category values one level lower.
      if (count($case) > 1) {
        foreach ($case as $category) {
          $this->assertSame($expected[$category], $allowed_values[strtoupper($category)], sprintf('Allowed values for icons set "%s" are not as expected.', implode(', ', $case)));
        }
        continue;
      }
      $this->assertSame($expected[$case[0]], $allowed_values, sprintf('Allowed values for icons set "%s" are not as expected.', implode(', ', $case)));
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
        'arrow-down' => 'Arrow-down',
        'arrow-left' => 'Arrow-left',
        'audio' => 'Audio',
        'book' => 'Book',
        'brochure' => 'Brochure',
        'budget' => 'Budget',
        'calendar' => 'Calendar',
        'camera' => 'Camera',
        'chain' => 'Chain',
        'check-filled' => 'Check-filled',
        'check' => 'Check',
        'clock-filled' => 'Clock-filled',
        'clock' => 'Clock',
        'close-filled' => 'Close-filled',
        'close-outline' => 'Close-outline',
        'close' => 'Close',
        'copy' => 'Copy',
        'corner-arrow' => 'Corner-arrow',
        'crosshair' => 'Crosshair',
        'data' => 'Data',
        'digital' => 'Digital',
        'document' => 'Document',
        'download' => 'Download',
        'edit' => 'Edit',
        'email' => 'Email',
        'energy' => 'Energy',
        'error-outline' => 'Error-outline',
        'error' => 'Error',
        'euro' => 'Euro',
        'event' => 'Event',
        'external-events' => 'External-events',
        'external' => 'External',
        'eye' => 'Eye',
        'faq' => 'Faq',
        'feedback' => 'Feedback',
        'file-blank' => 'File-blank',
        'file' => 'File',
        'folder' => 'Folder',
        'fullscreen' => 'Fullscreen',
        'global' => 'Global',
        'growth' => 'Growth',
        'hamburger' => 'Hamburger',
        'image' => 'Image',
        'infographic' => 'Infographic',
        'information-outline' => 'Information-outline',
        'information-round' => 'Information-round',
        'information' => 'Information',
        'laco-filled' => 'Laco-filled',
        'laco' => 'Laco',
        'list' => 'List',
        'livestreaming' => 'Livestreaming',
        'location-filled' => 'Location filled',
        'location' => 'Location',
        'log-in-outline' => 'Log-in-outline',
        'log-in' => 'Log-in',
        'logged-in' => 'Logged-in',
        'minus-outline' => 'Minus-outline',
        'minus' => 'Minus',
        'move' => 'Move',
        'multiple-files' => 'Multiple-files',
        'notification-active' => 'Notification-active',
        'notification' => 'Notification',
        'organigram' => 'Organigram',
        'package' => 'Package',
        'pause-filled' => 'Pause-filled',
        'pause-outline' => 'Pause-outline',
        'pause' => 'Pause',
        'play-filled' => 'Play-filled',
        'play-outline' => 'Play-outline',
        'play' => 'Play',
        'plus-filled' => 'Plus-filled',
        'plus-outline' => 'Plus-outline',
        'plus' => 'Plus',
        'presentation' => 'Presentation',
        'print' => 'Print',
        'refresh' => 'Refresh',
        'regulation' => 'Regulation',
        'rss' => 'Rss',
        'search' => 'Search',
        'settings' => 'Settings',
        'share-filled' => 'Share-filled',
        'share' => 'Share',
        'shopping-bag' => 'Shopping-bag',
        'solid-arrow' => 'Solid-arrow',
        'sort-carets' => 'Sort-carets',
        'spinner' => 'Spinner',
        'spreadsheet' => 'Spreadsheet',
        'star-filled' => 'Star-filled',
        'star-outline' => 'Star-outline',
        'tag' => 'Tag',
        'trash' => 'Trash',
        'upload' => 'Upload',
        'warning-outline' => 'Warning-outline',
        'warning-round' => 'Warning-round',
        'warning' => 'Warning',
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
