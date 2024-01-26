<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\oe_content_event_person_reference\Entity\EventSpeaker;
use Drupal\oe_content_person\Entity\PersonJob;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\IconsTextAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\SocialMediaLinksAssert;
use Drupal\Tests\oe_theme\PatternAssertions\TextFeaturedMediaAssert;
use Drupal\Tests\oe_theme\PatternAssertions\TimelineAssert;
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
  protected static $modules = [
    'oe_theme_content_event',
    'oe_content_event_person_reference',
    'oe_multilingual',
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
      ->grantPermission('view published oe_event_programme')
      ->save();

    $field = FieldConfig::create([
      'label' => 'Speakers',
      'field_name' => 'oe_event_speakers',
      'entity_type' => 'node',
      'bundle' => 'oe_event',
      'settings' => [],
      'required' => FALSE,
    ]);
    $field->save();

    $view_display = EntityViewDisplay::load('node.oe_event.full');
    $view_display->setComponent('oe_event_speakers', [
      'type' => 'entity_reference_revisions_entity_view',
      'label' => 'hidden',
      'settings' => [
        'view_mode' => 'default',
      ],
    ])->save();
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
    $en_file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
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
        'timezone' => 'Europe/Brussels',
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
      $this->assertSession()->elementExists('css', 'figure[class="ecl-media-container__figure"] picture[class="ecl-picture ecl-media-container__picture"] img[class="ecl-media-container__media"][src*="' . $file_urls[$node_langcode] . '"][alt="default ' . $node_langcode . ' alt"]');
    }

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', 'figure[class="ecl-media-container__figure"] picture[class="ecl-picture ecl-media-container__picture"] img[class="ecl-media-container__media"][src*="' . $file_urls['en'] . '"][alt="default en alt"]');
  }

  /**
   * Tests that the Event page renders correctly.
   */
  public function testEventRendering(): void {
    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $start_date = (clone $static_time)->modify('+ 10 days');

    // Create an Event node with required fields only.
    $node = $this->getStorage('node')->create([
      'type' => 'oe_event',
      'title' => 'Test event node',
      'oe_event_type' => 'http://publications.europa.eu/resource/authority/public-event-type/COMPETITION_AWARD_CEREMONY',
      'oe_teaser' => 'Event teaser',
      'oe_subject' => 'http://data.europa.eu/uxp/114',
      'oe_event_status' => 'as_planned',
      'oe_event_dates' => [
        'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'end_value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'timezone' => 'Europe/Brussels',
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
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header');
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
          'text' => 'Council of the European Union',
          'size' => 'm',
        ], [
          'icon' => 'calendar',
          'text' => '27 February 2020, 15:00 CET',
          'size' => 'm',
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
          'body' => 'Thursday 27 February 2020, 15:00 CET',
        ], [
          'label' => 'Languages',
          'body' => 'Estonian, French',
        ],
      ],
    ];
    $field_list_html = $practical_list_content->getOuterHtml();
    $field_list_assert->assertPattern($field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('horizontal', $field_list_html);
    // The event didn't start yet so no status message should be displayed.
    $this->assertSession()->elementNotExists('css', 'div.ecl-notification.ecl-u-mb-2xl');

    // Check case when start and end dates are different in another timezone.
    $end_date = (clone $static_time)->modify('+ 20 days');
    $node->set('oe_event_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'timezone' => 'Europe/Brussels',
    ]);
    // Add 'Who should attend' field value.
    $node->set('oe_event_who_should_attend', 'Types of audiences that this event targets');
    $node->save();

    $this->drupalGet($node->toUrl());

    $icons_text_expected_values = [
      'items' => [
        [
          'icon' => 'file',
          'text' => 'Council of the European Union',
          'size' => 'm',
        ], [
          'icon' => 'calendar',
          'text' => "27 February 2020, 15:00 CET - 8 March 2020, 15:00 CET",
          'size' => 'm',
        ],
      ],
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'When',
          'body' => "Thursday 27 February 2020, 15:00 CET - Sunday 8 March 2020, 15:00 CET",
        ], [
          'label' => 'Who should attend',
          'body' => 'Types of audiences that this event targets',
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
          'body' => 'event_venue Address event_venue, 1001 <Brussels>, Belgium',
        ], [
          'label' => 'When',
          'body' => "Thursday 27 February 2020, 15:00 CET - Sunday 8 March 2020, 15:00 CET",
        ], [
          'label' => 'Who should attend',
          'body' => 'Types of audiences that this event targets',
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
      'size' => 'm',
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert address in Venue using country only.
    $venue_entity->set('oe_address', ['country_code' => 'MX'])->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][0]['body'] = 'event_venue Mexico';
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values['items'][2]['text'] = 'Mexico';
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert "Online only" field replaces the "Venue".
    $node->set('oe_event_online_only', TRUE)->save();
    $this->drupalGet($node->toUrl());
    $field_list_expected_values['items'][0]['body'] = 'Online only';
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values['items'][2]['text'] = 'Online only';
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert "Internal organiser" field.
    $node->set('oe_event_organiser_is_internal', TRUE);
    $node->set('oe_event_organiser_internal', 'http://publications.europa.eu/resource/authority/corporate-body/AASM');
    $node->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][4] = [
      'label' => 'Organiser',
      'body' => 'Associated African States and Madagascar',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Organiser name" field.
    $node->set('oe_event_organiser_is_internal', FALSE);
    $node->set('oe_event_organiser_name', 'External organiser')->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][4] = [
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
    $node->set('oe_event_online_description', 'Online event description');
    $node->set('oe_event_online_dates', [
      'value' => $online_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $online_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'timezone' => 'Europe/Brussels',
    ])->save();
    $this->drupalGet($node->toUrl());

    // The livestream is upcoming so only the start date is available.
    $this->assertSession()->pageTextNotContains('Online event description');
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Where',
          'body' => 'Online only',
        ], [
          'label' => 'When',
          'body' => "Thursday 27 February 2020, 15:00 CET - Sunday 8 March 2020, 15:00 CET",
        ], [
          'label' => 'Livestream',
          'body' => 'Starts on Tuesday 17 March 2020, 15:00 CET',
        ], [
          'label' => 'Who should attend',
          'body' => 'Types of audiences that this event targets',
        ], [
          'label' => 'Languages',
          'body' => 'Estonian, French',
        ], [
          'label' => 'Organiser',
          'body' => 'External organiser',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values['items'][3] = [
      'icon' => 'livestreaming',
      'text' => 'Live streaming available',
      'size' => 'm',
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    // Assert changing type of "Online type".
    $node->set('oe_event_online_type', 'livestream')->save();
    $this->drupalGet($node->toUrl());

    // Assert "Event website" field.
    $node->set('oe_event_website', [
      'uri' => 'http://www.example.com/event',
      'title' => 'Event website',
    ])->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][6] = [
      'label' => 'Website',
      'body' => 'Event website',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());
    $event_website_link_icon = $this->assertSession()->elementExists('css', 'dl.ecl-description-list dd a.ecl-link svg.ecl-icon.ecl-icon--2xs.ecl-link__icon');
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $event_website_link_icon->getHtml());

    // Assert "Registration capacity" field.
    $node->set('oe_event_registration_capacity', 'event registration capacity')->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][7] = [
      'label' => 'Number of seats',
      'body' => 'event registration capacity',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Entrance fee" field.
    $node->set('oe_event_entrance_fee', 'entrance fee')->save();
    $this->drupalGet($node->toUrl());

    $field_list_expected_values['items'][8] = [
      'label' => 'Entrance fee',
      'body' => 'entrance fee',
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    // Assert "Registration URL" field.
    $node->set('oe_event_registration_url', 'http://www.example.com/registation');
    $node->save();
    $this->drupalGet($node->toUrl());

    $registration_content = $this->assertSession()->elementExists('css', '#event-registration-block');
    $this->assertRegistrationButtonEnabled($registration_content, 'Register here', 'http://www.example.com/registation', TRUE);

    // Report or media content are not shown when event is still ongoing.
    $node->set('oe_event_report_summary', 'Event report summary');
    $node->set('oe_event_report_text', 'Event report text');
    $node->set('oe_event_media', [
      $this->createMediaImage('first_image')->id(),
      $this->createMediaImage('second_image')->id(),
    ])->save();
    $node->set('oe_event_media_more_link', [
      'uri' => 'http://www.example.com',
      'title' => 'Main link for media items',
    ]);
    $node->set('oe_event_media_more_description', 'More media links');
    $node->save();
    $this->drupalGet($node->toUrl());

    $this->assertSession()->pageTextNotContains('Event report summary');
    $this->assertSession()->pageTextNotContains('Event report text');
    $this->assertSession()->elementNotExists('css', 'section.ecl-gallery');
    $this->assertSession()->elementNotExists('css', 'div#event-details div.ecl-col-12 h2.ecl-u-type-heading-2.ecl-u-mt-2xl.ecl-u-mb-l');
    $this->assertSession()->linkNotExistsExact('Main link for media items');
    $this->assertSession()->pageTextNotContains('More media links');

    // Assert "Description" title is not rendered unless there is a body text.
    $this->assertSession()->pageTextNotContains('Description');
    // Fill in "Featured media" field.
    $media_image = $this->createMediaImage('event_featured_media');
    $node->set('oe_event_featured_media', [$media_image])->save();
    $this->drupalGet($node->toUrl());
    // Assert "Description" field group contains only the heading and media.
    $description_content = $this->assertSession()->elementExists('css', 'article > div > div:nth-child(3)');
    $text_featured = new TextFeaturedMediaAssert();
    // Caption should not be rendered if the legend is not provided.
    $text_featured_expected_values = [
      'title' => 'Description',
      'text' => NULL,
      'caption' => NULL,
      'image' => [
        'alt' => 'Alternative text event_featured_media',
        'src' => 'event_featured_media.png',
      ],
    ];
    // If the "Full text" field is not filled in, we render using the
    // "right_simple" variant with the image on the left.
    $text_featured->assertVariant('right_simple', $description_content->getHtml());
    $text_featured->assertPattern($text_featured_expected_values, $description_content->getHtml());
    // Fill in "Featured media legend" field.
    $node->set('oe_event_featured_media_legend', 'Event featured media legend')->save();
    $this->drupalGet($node->toUrl());
    // Assert caption is rendered.
    $text_featured_expected_values['caption'] = 'Event featured media legend';
    $text_featured->assertPattern($text_featured_expected_values, $description_content->getHtml());

    // Fill in the "Full text" field too.
    $node->set('body', 'Event full text')->save();
    $this->drupalGet($node->toUrl());
    $text_featured_expected_values['text'] = 'Event full text';
    // Assert we render using the "left_simple" variant when "Full text" is
    // provided.
    $text_featured->assertVariant('left_simple', $description_content->getHtml());
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
      'timezone' => 'Europe/Brussels',
    ])->save();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonDisabled($registration_content, 'Register here');
    $registration_info_content = $this->assertSession()->elementExists('css', 'p.ecl-u-type-paragraph.ecl-u-type-color-dark-100');
    $this->assertEquals('Registration will open in 1 day. You can register from 18 February 2020, 15:00 CET, until 21 February 2020, 15:00 CET.', $registration_info_content->getText());

    // Assert "Registration date" field when registration will start today in
    // one hour.
    $static_time = new DrupalDateTime('2020-02-18 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonDisabled($registration_content, 'Register here');
    $this->assertEquals('Registration will open today, 18 February 2020, 15:00 CET.', $registration_info_content->getText());

    // Assert "Registration date" field when registration is in progress.
    $static_time = new DrupalDateTime('2020-02-20 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonEnabled($registration_content, 'Register here', 'http://www.example.com/registation', TRUE);
    $this->assertEquals('Book your seat, 1 day left to register, registration will end on 21 February 2020, 15:00 CET', $registration_info_content->getText());

    // Assert "Registration date" field when registration will finish today in
    // one hour.
    // Set the Registration URL to an internal path.
    $node->set('oe_event_registration_url', 'https://www.europa.eu.com/registation');
    $node->save();
    $static_time = new DrupalDateTime('2020-02-21 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertRegistrationButtonEnabled($registration_content, 'Register here', 'https://www.europa.eu.com/registation', FALSE);
    $this->assertEquals('Book your seat, the registration will end today, 21 February 2020, 15:00 CET', $registration_info_content->getText());

    // Assert "Registration date" field in the past.
    $static_time = new DrupalDateTime('2020-02-24 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $this->assertSession()->elementNotExists('css', 'a.ecl-u-mt-2xl.ecl-link.ecl-link--cta', $registration_content);
    $this->assertEquals('Registration period ended on Friday 21 February 2020, 15:00 CET', $registration_info_content->getText());

    // Assert "Report text" and "Summary for report" fields when event is
    // finished.
    $static_time = new DrupalDateTime('2020-04-15 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    // Assert ongoing livestream block.
    $online_heading = $this->assertSession()->elementExists('css', 'h3', $details_content);
    $this->assertEquals('Livestream', $online_heading->getText());
    $online_description = $this->assertSession()->elementExists('css', 'div > div:nth-of-type(1) > .ecl', $details_content);
    $this->assertEquals('Online event description', $online_description->getText());
    $online_button = $this->assertSession()->elementExists('css', 'a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-u-mt-l.ecl-u-mb-l.ecl-u-d-inline-block', $details_content);
    $this->assertEquals('Link to online event', $online_button->find('css', 'span.ecl-link__label')->getText());
    $this->assertEquals('http://www.example.com/online_link', $online_button->getAttribute('href'));
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $online_button->find('css', 'svg.ecl-icon.ecl-icon--2xs.ecl-link__icon')->getHtml());

    $description_summary = $this->assertSession()->elementExists('css', 'div > div:nth-of-type(2) .ecl', $details_content);
    $this->assertEquals('Event report summary', $description_summary->getText());

    $text_featured_expected_values['title'] = 'Report';
    $text_featured_expected_values['text'] = 'Event report text';
    $description_content = $this->assertSession()->elementExists('css', 'article > div > div:nth-of-type(4)');
    $text_featured->assertPattern($text_featured_expected_values, $description_content->getHtml());

    // Assert media gallery rendering.
    $this->assertSession()->elementTextContains('css', 'div#event-media h2.ecl-u-type-heading-2.ecl-u-mt-2xl.ecl-u-mb-l', 'Media');
    $gallery = $this->assertSession()->elementExists('css', 'section.ecl-gallery');
    $items = $gallery->findAll('css', 'li.ecl-gallery__item');
    $this->assertCount(2, $items);
    $first_item = $items[0]->find('css', 'img');
    $this->assertEquals('Alternative text first_image', $first_item->getAttribute('alt'));
    $this->assertStringContainsString('placeholder_first_image.png', $first_item->getAttribute('src'));
    $caption = $items[0]->find('css', '.ecl-gallery__description');
    $this->assertStringContainsString('Test image first_image', $caption->getOuterHtml());
    $this->assertEmpty($caption->find('css', '.ecl-gallery__meta')->getText());
    // Assert media links.
    $this->assertSession()->linkExistsExact('Main link for media items');
    $more_media_link_icon = $this->assertSession()->elementExists('css', 'div#event-media a.ecl-link svg.ecl-icon.ecl-icon--2xs.ecl-link__icon');
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $more_media_link_icon->getHtml());
    $this->assertSession()->pageTextContainsOnce('More media links');

    // Assert that summary and description fields are not displayed anymore.
    $this->assertSession()->pageTextNotContains('Event description summary');
    $this->assertSession()->pageTextNotContains('Event full text');
    $this->assertSession()->pageTextNotContains('<h2 class="ecl-u-type-heading-2 ecl-u-mt-2xl ecl-u-mt-m-3xl ecl-u-mb-l">Description</h2>');
    $this->assertSession()->elementNotExists('css', '#event-registration-block');

    // Assert the correct status information is displayed when the event has
    // finished but the livestream is ongoing.
    $status_container = $this->assertSession()->elementExists('css', 'div.ecl-notification.ecl-notification--warning.ecl-u-mb-2xl');
    // Assert the livestream icon is rendered.
    $icon = $status_container->find('css', 'svg.ecl-icon.ecl-icon--l.ecl-notification__icon use');
    $this->assertStringContainsString('livestreaming', $icon->getAttribute('xlink:href'));
    // Assert the message.
    $this->assertStringContainsString('This event has ended, but the livestream is ongoing.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());

    // Assert "Event contact" field.
    $contact_entity_general1 = $this->createContactEntity('first_general_contact');
    $contact_entity_general2 = $this->createContactEntity('second_general_contact');
    $contact_entity_press1 = $this->createContactEntity('first_press_contact', 'oe_press');
    $contact_entity_press2 = $this->createContactEntity('second_press_contact', 'oe_press');
    $node->set('oe_event_contact', [
      $contact_entity_general1,
      $contact_entity_general2,
      $contact_entity_press1,
      $contact_entity_press2,
    ])->save();
    $this->drupalGet($node->toUrl());

    $event_contacts_content = $this->assertSession()->elementExists('css', '#event-contacts');
    $event_contacts_header = $this->assertSession()->elementExists('css', 'h2.ecl-u-type-heading-2.ecl-u-mt-2xl.ecl-u-mt-m-3xl.ecl-u-mb-l', $event_contacts_content);
    $this->assertEquals('Contacts', $event_contacts_header->getText());

    $general_contacts_content = $this->assertSession()->elementExists('css', '#event-contacts-general', $event_contacts_content);
    $this->assertContactHeader($general_contacts_content, 'General contact');
    $general_contacts_border = $general_contacts_content->findAll('css', '.ecl-u-mb-xl.ecl-u-pb-xl.ecl-u-border-bottom.ecl-u-border-color-neutral-40');
    $this->assertCount(1, $general_contacts_border);
    $general_contacts = $general_contacts_content->findAll('css', '.ecl-row.ecl-u-mv-xl');
    $this->assertContactDefaultRender($general_contacts[0], 'first_general_contact');
    $this->assertContactDefaultRender($general_contacts[1], 'second_general_contact');

    $press_contacts_content = $this->assertSession()->elementExists('css', '#event-contacts-press', $event_contacts_content);
    $this->assertContactHeader($press_contacts_content, 'Press contact');
    $press_contacts_border = $press_contacts_content->findAll('css', '.ecl-u-mb-xl.ecl-u-pb-xl.ecl-u-border-bottom.ecl-u-border-color-neutral-40');
    $this->assertCount(1, $press_contacts_border);
    $press_contacts = $press_contacts_content->findAll('css', '.ecl-row.ecl-u-mv-xl');
    $this->assertContactDefaultRender($press_contacts[0], 'first_press_contact');
    $this->assertContactDefaultRender($press_contacts[1], 'second_press_contact');

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
    $contact_entity_general1->setUnpublished()->save();
    $contact_entity_general2->setUnpublished()->save();
    $contact_entity_press1->setUnpublished()->save();
    $contact_entity_press2->setUnpublished()->save();
    $venue_entity->setUnpublished()->save();
    $node->set('oe_event_online_only', FALSE)->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', '#event-contacts');

    // Livestream is ongoing so both the start date and livestream link are
    // available.
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'When',
          'body' => "Thursday 27 February 2020, 15:00 CET - Sunday 8 March 2020, 15:00 CET",
        ], [
          'label' => 'Livestream',
          'body' => 'Starts on Tuesday 17 March 2020, 15:00 CETLink to online event',
        ], [
          'label' => 'Who should attend',
          'body' => 'Types of audiences that this event targets',
        ], [
          'label' => 'Languages',
          'body' => 'Estonian, French',
        ], [
          'label' => 'Organiser',
          'body' => 'External organiser',
        ], [
          'label' => 'Website',
          'body' => 'Event website',
        ], [
          'label' => 'Number of seats',
          'body' => 'event registration capacity',
        ], [
          'label' => 'Entrance fee',
          'body' => 'entrance fee',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values = [
      'items' => [
        [
          'icon' => 'file',
          'text' => 'Council of the European Union',
          'size' => 'm',
        ], [
          'icon' => 'calendar',
          'text' => "27 February 2020, 15:00 CET - 8 March 2020, 15:00 CET",
          'size' => 'm',
        ], [
          'icon' => 'livestreaming',
          'text' => 'Live streaming available',
          'size' => 'm',
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

    // Assert gallery rendering.
    $this->assertSession()->elementTextContains('css', 'div#event-details div.ecl-col-12 h2.ecl-u-type-heading-2.ecl-u-mt-2xl.ecl-u-mb-l', 'Media');
    $gallery = $this->assertSession()->elementExists('css', 'section.ecl-gallery');
    $items = $gallery->findAll('css', 'li.ecl-gallery__item');
    $this->assertCount(2, $items);
    $first_item = $items[0]->find('css', 'img');
    $this->assertEquals('Alternative text first_image', $first_item->getAttribute('alt'));
    $this->assertStringContainsString('placeholder_first_image.png', $first_item->getAttribute('src'));
    $caption = $items[0]->find('css', '.ecl-gallery__description');
    $this->assertStringContainsString('Test image first_image', $caption->getOuterHtml());
    $this->assertEmpty($caption->find('css', '.ecl-gallery__meta')->getText());

    $session1 = $this->createProgrammeItemEntity('Session 1');
    $session2 = $this->createProgrammeItemEntity('Session 2');
    $session3 = $this->createProgrammeItemEntity('Session 3');

    // Assert Programme field.
    $node->set('oe_event_programme', [$session1, $session2, $session3])->save();
    $this->drupalGet($node->toUrl());

    $timeline_assert = new TimelineAssert();
    $timeline_expected_values = [
      'items' => [
        [
          'label' => '17 Feb 2020, 02:00 PM - 27 Feb 2020, 02:00 PM UTC',
          'title' => 'Session 1',
          'body' => 'Description Session 1',
        ], [
          'label' => '17 Feb 2020, 02:00 PM - 27 Feb 2020, 02:00 PM UTC',
          'title' => 'Session 2',
          'body' => 'Description Session 2',
        ], [
          'label' => '17 Feb 2020, 02:00 PM - 27 Feb 2020, 02:00 PM UTC',
          'title' => 'Session 3',
          'body' => 'Description Session 3',
        ],
      ],
    ];
    $timeline_content = $this->assertSession()->elementExists('css', 'article div');
    $timeline_html = $timeline_content->getOuterHtml();
    $timeline_assert->assertPattern($timeline_expected_values, $timeline_html);

    $session3->set('oe_event_programme_dates', [
      'value' => '2019-11-15T11:00:00',
      'end_value' => '2019-11-15T15:00:00',
      'timezone' => 'UTC',
    ]);
    $session3->save();

    $session1->set('oe_event_programme_dates', [
      'value' => '2019-11-15T22:00:00',
      'end_value' => '2019-11-15T23:00:00',
      'timezone' => 'UTC',
    ]);
    $session1->save();
    $this->drupalGet($node->toUrl());

    $timeline_assert = new TimelineAssert();
    $timeline_expected_values = [
      'items' => [
        [
          'label' => '15 Nov 2019, 11:00 AM - 15 Nov 2019, 03:00 PM UTC',
          'title' => 'Session 3',
          'body' => 'Description Session 3',
        ], [
          'label' => '10:00 PM - 11:00 PM UTC',
          'title' => 'Session 1',
          'body' => 'Description Session 1',
        ], [
          'label' => '17 Feb 2020, 02:00 PM - 27 Feb 2020, 02:00 PM UTC',
          'title' => 'Session 2',
          'body' => 'Description Session 2',
        ],
      ],
    ];
    $timeline_content = $this->assertSession()->elementExists('css', 'article div');
    $timeline_html = $timeline_content->getOuterHtml();
    $timeline_assert->assertPattern($timeline_expected_values, $timeline_html);

    $session3->set('oe_event_programme_dates', [
      'value' => '2019-11-14T21:00:00',
      'end_value' => '2019-11-14T22:00:00',
      'timezone' => 'UTC',
    ]);
    $session3->save();

    $session1->set('oe_event_programme_dates', [
      'value' => '2019-11-14T22:15:00',
      'end_value' => '2019-11-14T23:15:00',
      'timezone' => 'UTC',
    ]);
    $session1->save();
    $session2->set('oe_event_programme_dates', [
      'value' => '2019-11-15T22:00:00',
      'end_value' => '2019-11-15T23:00:00',
      'timezone' => 'UTC',
    ]);
    $session2->save();
    $this->drupalGet($node->toUrl());

    $timeline_assert = new TimelineAssert();
    $timeline_expected_values = [
      'items' => [
        [
          'label' => '14 Nov 2019,<br>09:00 PM - 10:00 PM UTC',
          'title' => 'Session 3',
          'body' => 'Description Session 3',
        ], [
          'label' => '10:15 PM - 11:15 PM UTC',
          'title' => 'Session 1',
          'body' => 'Description Session 1',
        ], [
          'label' => '15 Nov 2019,<br>10:00 PM - 11:00 PM UTC',
          'title' => 'Session 2',
          'body' => 'Description Session 2',
        ],
      ],
    ];
    $timeline_content = $this->assertSession()->elementExists('css', 'article div');
    $timeline_html = $timeline_content->getOuterHtml();
    $timeline_assert->assertPattern($timeline_expected_values, $timeline_html);

    // Create jobs for person entity.
    $person_job_1 = PersonJob::create([
      'type' => 'oe_default',
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS',
    ]);
    $person_job_1->save();
    $person_job_2 = PersonJob::create([
      'type' => 'oe_default',
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS_CHIEF',
    ]);
    $person_job_2->save();

    // Create Person node for assigning to Event speaker entity.
    /** @var \Drupal\node\Entity\Node $person */
    $values = [
      'type' => 'oe_person',
      'oe_person_first_name' => 'Mick',
      'oe_person_last_name' => 'Jagger',
      'oe_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'uid' => 0,
      'status' => 1,
    ];
    $person = Node::create($values);
    $person->set('oe_person_jobs', [$person_job_1, $person_job_2]);
    $person->save();

    $event_speaker = EventSpeaker::create([
      'oe_event_role' => 'event role 1',
      'type' => 'oe_default',
      'oe_person' => $person,
    ]);
    $event_speaker->save();

    // Add Event speakers for event node.
    $node->set('oe_event_speakers', [$event_speaker]);
    $node->save();

    $this->drupalGet($node->toUrl());
    $speakers = $this->assertSession()->elementExists('css', '.ecl-row.field-oe-event-speakers');
    $speakers_items = $speakers->findAll('css', '.ecl-u-d-flex.ecl-u-pv-m.ecl-u-border-bottom.ecl-u-border-color-neutral-40.ecl-col-12.ecl-col-m-6.ecl-col-l-4');
    $this->assertCount(1, $speakers_items);

    // Make sure that adding of additional Event speakers
    // is reflected on the full page.
    $node->set('oe_event_speakers', [$event_speaker, $event_speaker]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $speakers = $this->assertSession()->elementExists('css', '.ecl-row.field-oe-event-speakers');
    $speakers_items = $speakers->findAll('css', '.ecl-u-d-flex.ecl-u-pv-m.ecl-u-border-bottom.ecl-u-border-color-neutral-40.ecl-col-12.ecl-col-m-6.ecl-col-l-4');
    $this->assertCount(2, $speakers_items);
    $portrait = $this->assertSession()->elementExists('css', '.ecl-u-flex-shrink-0.ecl-u-mr-s.ecl-u-media-a-m.ecl-u-media-bg-size-contain.ecl-u-media-bg-repeat-none', $speakers_items[0]);
    // Assert default image of speaker.
    $this->assertStringContainsString('oe_theme/images/user_icon.svg', $portrait->getAttribute('style'));
    $meta = $this->assertSession()->elementExists('css', '.ecl-content-item__meta.ecl-u-type-s.ecl-u-type-color-dark-100.ecl-u-mb-s.ecl-u-type-uppercase', $speakers_items[0]);
    // Assert event role of speaker.
    $this->assertEquals('event role 1', $meta->getText());
    // Assert Person link.
    $link = $this->assertSession()->elementExists('css', '.ecl-link.ecl-link--standalone.ecl-u-type-bold', $speakers_items[0]);
    $this->assertStringContainsString($person->toUrl()->toString(), $link->getAttribute('href'));
    $this->assertEquals($person->label(), $link->getText());
    $person_jobs = $this->assertSession()->elementExists('css', '.ecl-u-type-s.ecl-u-type-color-dark-100.ecl-u-mt-s', $speakers_items[0]);
    // Assert person jobs.
    $this->assertEquals('Adviser, Chief Adviser', $person_jobs->getText());

    // Assert that changes in Event role are applied.
    $event_speaker->set('oe_event_role', 'event role 2');
    $event_speaker->save();
    $this->drupalGet($node->toUrl());
    $meta = $this->assertSession()->elementExists('css', '.ecl-content-item__meta.ecl-u-type-s.ecl-u-type-color-dark-100.ecl-u-mb-s.ecl-u-type-uppercase', $speakers_items[0]);
    $this->assertEquals('event role 2', $meta->getText());

    // Assert that changes in person job are applied.
    $person_job_1->set('oe_role_reference', 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS_COMMU');
    $person_job_1->save();
    $this->drupalGet($node->toUrl());
    $person_jobs = $this->assertSession()->elementExists('css', '.ecl-u-type-s.ecl-u-type-color-dark-100.ecl-u-mt-s', $speakers_items[0]);
    $this->assertEquals('Communication Adviser, Chief Adviser', $person_jobs->getText());

    // Assert that changes in person photo are applied.
    $portrait_media = $this->createMediaImage('person_portrait');
    $person->set('oe_person_photo', $portrait_media)->save();
    $this->drupalGet($node->toUrl());
    $portrait = $this->assertSession()->elementExists('css', '.ecl-u-flex-shrink-0.ecl-u-mr-s.ecl-u-media-a-m.ecl-u-media-bg-size-contain.ecl-u-media-bg-repeat-none', $speakers_items[0]);
    $this->assertStringContainsString('placeholder_person_portrait.png', $portrait->getAttribute('style'));

    // Event isn't started but livestream is ongoing.
    $start_date = (clone $static_time)->modify('+ 10 days');
    $end_date = (clone $static_time)->modify('+ 20 days');
    $node->set('oe_event_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'timezone' => 'Europe/Brussels',
    ]);
    $online_start_date = (clone $static_time)->modify('- 2 hours');
    $online_end_date = (clone $static_time)->modify('+ 2 hours');
    $node->set('oe_event_online_dates', [
      'value' => $online_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $online_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'timezone' => 'Europe/Brussels',
    ])->save();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('The livestream has started.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());

    // Set event and online dates to assert event status message.
    $node->set('oe_event_status_description', 'Event status message.');
    $start_date = (clone $static_time)->modify('+ 1 day');
    $end_date = (clone $static_time)->modify('+ 10 days');
    $node->set('oe_event_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'timezone' => 'Europe/Brussels',
    ]);
    $online_start_date = (clone $static_time)->modify('+ 3 days');
    $online_end_date = (clone $static_time)->modify('+ 5 days');
    $node->set('oe_event_online_dates', [
      'value' => $online_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $online_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'timezone' => 'Europe/Brussels',
    ])->save();
    $this->drupalGet($node->toUrl());
    // By default, message doesn't exist and the 'Status description' field is
    // not rendered for the livestream messages.
    $this->assertEmpty($status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title'));

    // Event is ongoing, but livestream is not.
    $static_time = new DrupalDateTime('2020-04-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('This event has started. The livestream will start at 18 April 2020, 23:00 AEST.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());

    // Event is ongoing and livestream also.
    $static_time = new DrupalDateTime('2020-04-18 20:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('This event has started. You can also watch it via livestream.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());

    // Event is ongoing but livestream is finished.
    $static_time = new DrupalDateTime('2020-04-20 22:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('The livestream has ended, but the event is ongoing.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());

    // Update the event status and assert the message updates correctly and the
    // 'Status description' field is displayed.
    $node->set('oe_event_status', 'postponed')->save();
    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', 'div.ecl-notification.ecl-notification--info.ecl-u-mb-2xl');
    $status_container = $this->assertSession()->elementExists('css', 'div.ecl-notification.ecl-notification--warning.ecl-u-mb-2xl');
    $this->assertStringContainsString('This event has been postponed.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());
    $this->assertSession()->elementTextContains('css', 'div.ecl-notification__content div.ecl-notification__description', 'Event status message.');
    $node->set('oe_event_status', 'cancelled')->save();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('This event has been cancelled.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());
    $this->assertSession()->elementTextContains('css', 'div.ecl-notification__content div.ecl-notification__description', 'Event status message.');
    $node->set('oe_event_status', 'rescheduled')->save();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('This event has been rescheduled.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());
    $this->assertSession()->elementTextContains('css', 'div.ecl-notification__content div.ecl-notification__description', 'Event status message.');

    // Empty the online field group.
    $node->set('oe_event_online_dates', [
      'value' => NULL,
      'end_value' => NULL,
    ]);
    $node->set('oe_event_online_type', '');
    $node->set('oe_event_online_link', ['uri' => '', 'title' => '']);
    $node->set('oe_event_online_description', '')->save();
    // Update event status to "As planned".
    $node->set('oe_event_status', 'as_planned')->save();
    $this->drupalGet($node->toUrl());
    // Assert the message updated.
    $this->assertSession()->elementNotExists('css', 'div.ecl-notification.ecl-notification--warning.ecl-u-mb-2xl');
    $status_container = $this->assertSession()->elementExists('css', 'div.ecl-notification.ecl-notification--info.ecl-u-mb-2xl');
    $this->assertStringContainsString('This event has started.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());
    // Assert that the 'Status description' field is not rendered for the
    // 'As planned' messages.
    $this->assertSession()->elementNotExists('css', 'div.ecl-notification__content div.ecl-notification__description');

    // Set current time after the event ends.
    $static_time = new DrupalDateTime('2020-05-15 13:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('This event has ended.', $status_container->find('css', 'div.ecl-notification__content div.ecl-notification__title')->getText());
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
   * @param bool $external
   *   Whether the registration link is external.
   */
  protected function assertRegistrationButtonEnabled(NodeElement $parent_element, string $text, string $link, bool $external): void {
    if ($external) {
      $rendered_button = $this->assertSession()->elementExists('css', 'span.ecl-u-mt-2xl.ecl-u-d-inline-block a.ecl-link.ecl-link--cta.ecl-link--icon', $parent_element);
      $this->assertEquals($text, $rendered_button->find('css', 'span.ecl-link__label')->getText());
      $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $rendered_button->find('css', 'svg.ecl-icon.ecl-icon--2xs.ecl-link__icon')->getHtml());
    }
    else {
      $this->assertSession()->elementNotExists('css', 'span.ecl-u-mt-2xl.ecl-u-d-inline-block a.ecl-link.ecl-link--cta.ecl-link--icon', $parent_element);
      $rendered_button = $this->assertSession()->elementExists('css', 'a.ecl-link.ecl-link--cta', $parent_element);
      $this->assertEquals($text, $rendered_button->getText());
    }
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
    $rendered_header = $this->assertSession()->elementExists('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-m.ecl-u-mb-m-l', $rendered_element);
    $this->assertEquals($title, $rendered_header->getText());
  }

}
