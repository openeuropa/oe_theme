<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\IconsTextAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\SocialMediaLinksAssert;
use Drupal\Tests\oe_theme\PatternAssertions\TextFeaturedMediaAssert;
use Drupal\Tests\Traits\Core\CronRunTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our Event content type render.
 *
 * @group batch1
 */
class ContentEventRenderTest extends ContentRenderTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'content_translation',
    'datetime_testing',
    'block',
    'system',
    'oe_theme_helper',
    'oe_theme_content_event',
    'oe_multilingual',
    'path',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Give anonymous users permission to view corporate entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published oe_venue')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the Event featured media renders the translated media.
   */
  public function testEventFeaturedMediaTranslation(): void {
    // Make event node and image media translatable.
    \Drupal::service('content_translation.manager')->setEnabled('node', 'oe_event', TRUE);
    \Drupal::service('content_translation.manager')->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->getStorage('field_config')->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    \Drupal::service('router.builder')->rebuild();

    // Create image media that we will use for the English translation.
    $en_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
    $bg_file->setPermanent();
    $bg_file->save();

    // Create a media entity of image media type.
    /** @var \Drupal\media\Entity\Media $media */
    $media = $this->getStorage('media')->create([
      'bundle' => 'image',
      'name' => 'Test image',
      'oe_media_image' => [
        'target_id' => $en_file->id(),
        'alt' => 'default en alt',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    // Add a Bulgarian translation.
    $media->addTranslation('bg', [
      'name' => 'Test image bg',
      'oe_media_image' => [
        'target_id' => $bg_file->id(),
        'alt' => 'default bg alt',
      ],
    ]);
    $media->save();

    // Create an Event node in English translation.
    $node = $this->getStorage('node')->create([
      'type' => 'oe_event',
      'title' => 'Test event node',
      'oe_event_type' => 'http://publications.europa.eu/resource/authority/public-event-type/COMPETITION_AWARD_CEREMONY',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
      'oe_event_dates' => [
        'value' => '2030-05-10T12:00:00',
        'end_value' => '2030-05-15T12:00:00',
      ],
      'oe_event_featured_media' => [
        'target_id' => (int) $media->id(),
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $node->addTranslation('bg', ['title' => 'Test event bg']);
    $node->save();

    $file_urls = [
      'bg' => $bg_file->createFileUrl(),
      'en' => $en_file->createFileUrl(),
    ];

    foreach ($node->getTranslationLanguages() as $node_langcode => $node_language) {
      $node = \Drupal::service('entity.repository')->getTranslationFromContext($node, $node_langcode);
      $this->drupalGet($node->toUrl());
      $this->assertSession()->elementExists('css', 'figure[class="ecl-media-container"] img[src*="' . $file_urls[$node_langcode] . '"][alt="default ' . $node_langcode . ' alt"]');
    }

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', 'figure[class="ecl-media-container"] img[src*="' . $file_urls['en'] . '"][alt="default en alt"]');
  }

  /**
   * Tests that the Event page renders correctly.
   */
  public function testEventRendering(): void {
    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $start_date = (clone $static_time)->modify('+ 10 days');

    // Create a Event node with required fields only.
    $node = $this->getStorage('node')->create([
      'type' => 'oe_event',
      'title' => 'Test event node',
      'oe_event_type' => 'http://publications.europa.eu/resource/authority/public-event-type/COMPETITION_AWARD_CEREMONY',
      'oe_teaser' => 'Event teaser',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_event_status' => 'as_planned',
      'oe_event_dates' => [
        'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'end_value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ],
      'oe_event_languages' => [
        ['target_id' => 'http://publications.europa.eu/resource/authority/language/EST'],
        ['target_id' => 'http://publications.europa.eu/resource/authority/language/FRA'],
      ],
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $page_header_assert = new PatternPageHeaderAssert();
    $page_header_expected_values = [
      'title' => 'Test event node',
      'meta' => ['Competitions and award ceremonies'],
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Check that we don't have blocks if fields are empty.
    $this->assertSession()->elementNotExists('css', '#event-registration-block');
    $this->assertSession()->elementNotExists('css', '#event-contacts');

    // Assert details.
    $details_content = $this->assertSession()->elementExists('css', '#event-details');
    $this->assertSession()->elementNotExists('css', '.ecl-body', $details_content);
    $details_list_content = $this->assertSession()->elementExists('css', '.ecl-col-12.ecl-col-m-6.ecl-u-mt-l.ecl-u-mt-m-none ul.ecl-unordered-list.ecl-unordered-list--no-bullet', $details_content);
    $icons_text_assert = new IconsTextAssert();
    $icons_text_expected_values = [
      'items' => [
        [
          'icon' => 'file',
          'text' => 'Financing',
        ], [
          'icon' => 'calendar',
          'text' => '28 February 2020, 01:00',
        ],
      ],
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert practical information.
    $practical_content = $this->assertSession()->elementExists('css', '#event-practical-information');
    $this->assertContentHeader($practical_content, 'Practical information');

    $practical_list_content = $this->assertSession()->elementExists('css', 'dl.ecl-description-list', $practical_content);
    $field_list_assert = new FieldListAssert();
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'When',
          'body' => 'Friday 28 February 2020, 01:00',
        ], [
          'label' => 'Languages',
          'body' => 'Estonian, French',
        ],
      ],
    ];
    $field_list_html = $practical_list_content->getOuterHtml();
    $field_list_assert->assertPattern($field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('horizontal', $field_list_html);

    // Check case when start and end dates are different.
    $end_date = (clone $static_time)->modify('+ 20 days');
    $node->set('oe_event_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $icons_text_expected_values = [
      'items' => [
        [
          'icon' => 'file',
          'text' => 'Financing',
        ], [
          'icon' => 'calendar',
          'text' => "28 February 2020, 01:00\n to 9 March 2020, 01:00",
        ],
      ],
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'When',
          'body' => "Friday 28 February 2020, 01:00\n to Monday 9 March 2020, 01:00",
        ], [
          'label' => 'Languages',
          'body' => 'Estonian, French',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert Introduction field.
    $node->set('oe_summary', 'Event introduction')->save();
    $this->drupalGet($node->toUrl());
    $page_header_expected_values['description'] = 'Event introduction';
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert "Venue" field.
    $venue_entity = $this->createVenueEntity('event_venue');
    $node->set('oe_event_venue', [$venue_entity])->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Where',
          'body' => "event_venue\n  Address event_venue, 1001 <Brussels>, Belgium",
        ], [
          'label' => 'When',
          'body' => "Friday 28 February 2020, 01:00\n to Monday 9 March 2020, 01:00",
        ], [
          'label' => 'Languages',
          'body' => 'Estonian, French',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values['items'][2] = [
      'icon' => 'location',
      'text' => '<Brussels>, Belgium',
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert address in Venue using country only.
    $venue_entity->set('oe_address', ['country_code' => 'MX'])->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][0]['body'] = "event_venue\n  Mexico";
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values['items'][2]['text'] = 'Mexico';
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert "Internal organiser" field.
    $node->set('oe_event_organiser_is_internal', TRUE);
    $node->set('oe_event_organiser_internal', 'http://publications.europa.eu/resource/authority/corporate-body/AASM');
    $node->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][3] = [
      'label' => 'Organiser',
      'body' => 'Associated African States and Madagascar',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Organiser name" field.
    $node->set('oe_event_organiser_is_internal', FALSE);
    $node->set('oe_event_organiser_name', 'External organiser')->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][3] = [
      'label' => 'Organiser',
      'body' => 'External organiser',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Online type", "Online link", "Online time" fields.
    $online_start_date = (clone $static_time)->modify('+ 1 months');
    $online_end_date = (clone $static_time)->modify('+ 2 months');
    $node->set('oe_event_online_type', 'facebook');
    $node->set('oe_event_online_link', [
      'uri' => 'http://www.example.com/online_link',
      'title' => 'Link to online event',
    ]);
    // The "Online description" field is not currently displayed.
    // @todo Assert its visibility as soon as the issue below will be fixed.
    // @see https://citnet.tech.ec.europa.eu/CITnet/jira/browse/EWPP-1063
    $node->set('oe_event_online_description', 'Online event description');
    $node->set('oe_event_online_dates', [
      'value' => $online_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $online_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][4] = [
      'label' => 'Live stream',
      'body' => 'Facebook',
    ];
    $field_list_expected_values['items'][5] = [
      'label' => 'Online link',
      'body' => 'Link to online event',
    ];
    $field_list_expected_values['items'][6] = [
      'label' => 'Online time',
      'body' => "18 March 2020, 01:00 AEDT\n to 18 April 2020, 00:00 AEST",
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values['items'][3] = [
      'icon' => 'livestreaming',
      'text' => 'Live streaming available',
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert changing type of "Online type".
    $node->set('oe_event_online_type', 'livestream')->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][4] = [
      'label' => 'Live stream',
      'body' => 'Livestream',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Event website" field.
    $node->set('oe_event_website', [
      'uri' => 'http://www.example.com/event',
      'title' => 'Event website',
    ])->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][7] = [
      'label' => 'Website',
      'body' => 'Event website',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Registration capacity" field.
    $node->set('oe_event_registration_capacity', 'event registration capacity')->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][8] = [
      'label' => 'Number of seats',
      'body' => 'event registration capacity',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Entrance fee" field.
    $node->set('oe_event_entrance_fee', 'entrance fee')->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][9] = [
      'label' => 'Entrance fee',
      'body' => 'entrance fee',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Registration URL" field.
    $node->set('oe_event_registration_url', 'http://www.example.com/registation');
    $node->save();
    $this->drupalGet($node->toUrl());

    $registration_content = $this->assertSession()->elementExists('css', '#event-registration-block');
    $this->assertRegistrationButtonEnabled($registration_content, 'Register here', 'http://www.example.com/registation');

    // Report isn't shown when event isn't completed yet.
    $node->set('oe_event_report_summary', 'Event report summary');
    $node->set('oe_event_report_text', 'Event report text');
    $node->save();
    $this->drupalGet($node->toUrl());

    $this->assertSession()->pageTextNotContains('Event report summary');
    $this->assertSession()->pageTextNotContains('Event report text');

    // Assert "Full text", "Featured media", "Featured media legend" fields
    // (these fields have to be filled all together).
    $node->set('body', 'Event full text');
    $node->set('oe_event_featured_media_legend', 'Event featured media legend');
    $media_image = $this->createMediaImage('event_featured_media');
    $node->set('oe_event_featured_media', [$media_image])->save();
    $this->drupalGet($node->toUrl());

    $description_content = $this->assertSession()->elementExists('css', 'article div div:nth-of-type(3)');
    $text_featured = new TextFeaturedMediaAssert();
    $text_featured_expected_values = [
      'title' => 'Description',
      'caption' => 'Event featured media legend',
      'text' => 'Event full text',
      'image' => [
        'alt' => 'Alternative text event_featured_media',
        'src' => 'event_featured_media.png',
      ],
    ];
    $text_featured->assertPattern($text_featured_expected_values, $description_content->getHtml());

    // Assert "Description summary" field value.
    $node->set('oe_event_description_summary', 'Event description summary')->save();
    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextContains('Event description summary');

    // Assert "Registration date" field when registration will start in future.
    $registration_start_date = (clone $static_time)->modify('+ 1 day');
    $registration_end_date = (clone $static_time)->modify('+ 4 days');
    $node->set('oe_event_registration_dates', [
      'value' => $registration_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $registration_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonDisabled($registration_content, 'Register here');
    $registration_info_content = $this->assertSession()->elementExists('css', 'p.ecl-u-type-paragraph.ecl-u-type-color-grey-75');
    $this->assertEquals('Registration will open in 1 day. You can register from 19 February 2020, 01:00, until 22 February 2020, 01:00.', $registration_info_content->getText());

    // Assert "Registration date" field when registration will start today in
    // one hour.
    $static_time = new DrupalDateTime('2020-02-18 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonDisabled($registration_content, 'Register here');
    $this->assertEquals('Registration will open today, 19 February 2020, 01:00.', $registration_info_content->getText());

    // Assert "Registration date" field when registration is in progress.
    $static_time = new DrupalDateTime('2020-02-20 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonEnabled($registration_content, 'Register here', 'http://www.example.com/registation');
    $this->assertEquals('Book your seat, 1 day left to register, registration will end on 22 February 2020, 01:00', $registration_info_content->getText());

    // Assert "Registration date" field when registration will finish today in
    // one hour.
    $static_time = new DrupalDateTime('2020-02-21 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonEnabled($registration_content, 'Register here', 'http://www.example.com/registation');
    $this->assertEquals('Book your seat, the registration will end today, 22 February 2020, 01:00', $registration_info_content->getText());

    // Assert "Registration date" field in the past.
    $static_time = new DrupalDateTime('2020-02-24 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertSession()->elementNotExists('css', 'a.ecl-u-mt-2xl.ecl-link.ecl-link--cta', $registration_content);
    $this->assertEquals('Registration period ended on Saturday 22 February 2020, 01:00', $registration_info_content->getText());

    // Assert "Report text" and "Summary for report" fields when event is
    // finished.
    $static_time = new DrupalDateTime('2020-04-15 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $description_summary = $this->assertSession()->elementExists('css', '.ecl', $details_content);
    $this->assertEquals('Event report summary', $description_summary->getText());

    $text_featured_expected_values['title'] = 'Report';
    $text_featured_expected_values['text'] = 'Event report text';
    $text_featured->assertPattern($text_featured_expected_values, $description_content->getHtml());

    $this->assertSession()->pageTextNotContains('Event description summary');
    $this->assertSession()->pageTextNotContains('Event full text');
    $this->assertSession()->pageTextNotContains('<h2 class="ecl-u-type-heading-2 ecl-u-mt-2xl ecl-u-mt-m-3xl ecl-u-mb-l">Description</h2>');
    $this->assertSession()->elementNotExists('css', '#event-registration-block');

    // Assert "Event contact" field.
    $contact_entity_general = $this->createContactEntity('general_contact');
    $contact_entity_press = $this->createContactEntity('press_contact', 'oe_press');
    $node->set('oe_event_contact', [
      $contact_entity_general,
      $contact_entity_press,
    ])->save();
    $this->drupalGet($node->toUrl());

    $event_contacts_content = $this->assertSession()->elementExists('css', '#event-contacts');
    $event_contacts_header = $this->assertSession()->elementExists('css', 'h2.ecl-u-type-heading-2.ecl-u-type-color-black.ecl-u-mt-2xl.ecl-u-mt-m-3xl.ecl-u-mb-l', $event_contacts_content);
    $this->assertEquals('Contacts', $event_contacts_header->getText());

    $general_contacts_content = $this->assertSession()->elementExists('css', '#event-contacts-general', $event_contacts_content);
    $this->assertContactHeader($general_contacts_content, 'General contact');
    $this->assertContactDetailsRender($general_contacts_content, 'general_contact');

    $press_contacts_content = $this->assertSession()->elementExists('css', '#event-contacts-press', $event_contacts_content);
    $this->assertContactHeader($press_contacts_content, 'Press contact');
    $this->assertContactDetailsRender($press_contacts_content, 'press_contact');

    // Assert "Social media links" links.
    $node->set('oe_social_media_links', [
      [
        'uri' => 'http://www.example.com/event_facebook',
        'title' => 'Event facebook link',
        'link_type' => 'facebook',
      ], [
        'uri' => 'http://www.example.com/event_instagram',
        'title' => 'Event instagram link',
        'link_type' => 'instagram',
      ],
    ])->save();
    $this->drupalGet($node->toUrl());

    $social_links_assert = new SocialMediaLinksAssert();
    $social_links_expected_values = [
      'title' => 'Social media',
      'links' => [
        [
          'service' => 'facebook',
          'label' => 'Event facebook link',
          'url' => 'http://www.example.com/event_facebook',
        ], [
          'service' => 'instagram',
          'label' => 'Event instagram link',
          'url' => 'http://www.example.com/event_instagram',
        ],
      ],
    ];
    $social_links_content = $this->assertSession()->elementExists('css', '#event-practical-information .ecl-social-media-follow');
    $social_links_html = $social_links_content->getOuterHtml();
    $social_links_assert->assertPattern($social_links_expected_values, $social_links_html);
    $social_links_assert->assertVariant('horizontal', $social_links_html);

    // Unpublished Venues and Contacts are not visible for the visitors.
    $contact_entity_general->setUnpublished()->save();
    $contact_entity_press->setUnpublished()->save();
    $venue_entity->setUnpublished()->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', '#event-contacts');

    $field_list_expected_values['items'][1] = [
      'label' => 'When',
      'body' => "Friday 28 February 2020, 01:00\n to Monday 9 March 2020, 01:00",
    ];
    unset($field_list_expected_values['items'][0]);
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values = [
      'items' => [
        [
          'icon' => 'file',
          'text' => 'Financing',
        ], [
          'icon' => 'calendar',
          'text' => "28 February 2020, 01:00\n to 9 March 2020, 01:00",
        ], [
          'icon' => 'livestreaming',
          'text' => 'Live streaming available',
        ],
      ],
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Verify that the event renders correctly when a non-existing event type
    // is set.
    $node->set('oe_event_type', 'http://publications.europa.eu/resource/authority/public-event-type/OP_DATPRO')
      ->save();
    $this->drupalGet($node->toUrl());
    $page_header_expected_values = [
      'title' => 'Test event node',
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());
  }

  /**
   * Asserts enabled registration button.
   *
   * @param \Behat\Mink\Element\NodeElement $parent_element
   *   Parent element.
   * @param string $text
   *   Button text.
   * @param string $link
   *   Button link.
   */
  protected function assertRegistrationButtonEnabled(NodeElement $parent_element, string $text, string $link): void {
    $rendered_button = $this->assertSession()->elementExists('css', 'a.ecl-u-mt-2xl.ecl-link.ecl-link--cta', $parent_element);
    $this->assertEquals($text, $rendered_button->getText());
    $this->assertEquals($link, $rendered_button->getAttribute('href'));
  }

  /**
   * Asserts disabled registration button.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Parent element.
   * @param string $text
   *   Button text.
   */
  protected function assertRegistrationButtonDisabled(NodeElement $element, string $text): void {
    $rendered_button = $this->assertSession()->elementExists('css', 'button.ecl-button.ecl-button--call.ecl-u-mt-2xl.ecl-link.ecl-link--cta', $element);
    $this->assertEquals($text, $rendered_button->getText());
    $this->assertTrue($rendered_button->hasAttribute('disabled'));
  }

  /**
   * Asserts header of the contact block.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_element
   *   Rendered element.
   * @param string $title
   *   Expected title.
   */
  protected function assertContactHeader(NodeElement $rendered_element, string $title): void {
    $rendered_header = $this->assertSession()->elementExists('css', 'h3.ecl-u-type-heading-3.ecl-u-type-color-black.ecl-u-mt-none.ecl-u-mb-m.ecl-u-mb-m-l', $rendered_element);
    $this->assertEquals($title, $rendered_header->getText());
  }

  /**
   * Asserts rendering of Contact entity using Details view mode.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $name
   *   Name of the Contact entity.
   */
  protected function assertContactDetailsRender(NodeElement $element, string $name): void {
    $field_list_assert = new FieldListAssert();
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Name',
          'body' => $name,
        ], [
          'label' => 'Email',
          'body' => "$name@example.com",
        ], [
          'label' => 'Phone number',
          'body' => "Phone number $name",
        ], [
          'label' => 'Address',
          'body' => "Address $name, 1001 Brussels, Belgium",
        ],
      ],
    ];
    $content = $this->assertSession()->elementExists('css', 'dl.ecl-description-list', $element);
    $field_list_html = $content->getOuterHtml();
    $field_list_assert->assertPattern($field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('horizontal', $field_list_html);

    // Assert "Social media links" links.
    $social_links_assert = new SocialMediaLinksAssert();
    $social_links_expected_values = [
      'title' => 'Social media',
      'links' => [
        [
          'service' => 'facebook',
          'label' => "Social media $name",
          'url' => "http://www.example.com/social_media_$name",
        ],
      ],
    ];
    $social_links_content = $this->assertSession()->elementExists('css', '.ecl-u-mt-l', $element);
    $social_links_html = $social_links_content->getHtml();
    $social_links_assert->assertPattern($social_links_expected_values, $social_links_html);
    $social_links_assert->assertVariant('horizontal', $social_links_html);
  }

}
