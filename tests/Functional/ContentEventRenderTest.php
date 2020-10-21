<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\IconsTextAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our Event content type render.
 *
 * @todo: Extend this test with ecl/markup rendering tests.
 */
class ContentEventRenderTest extends ContentRenderTestBase {

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
    'datetime_testing',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Give anonymous users permission to view Venue entities.
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
      'en' => $en_file->createFileUrl(),
      'bg' => $bg_file->createFileUrl(),
    ];

    foreach ($node->getTranslationLanguages() as $node_langcode => $node_language) {
      $node = \Drupal::service('entity.repository')->getTranslationFromContext($node, $node_langcode);
      $this->drupalGet($node->toUrl());
      $this->assertSession()->elementExists('css', 'figure[class="ecl-media-container"] img[src*="' . $file_urls[$node_langcode] . '"][alt="default ' . $node_langcode . ' alt"]');
    }
  }

  /**
   * Tests that the Event page renders correctly.
   */
  public function testEventRendering(): void {
    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $start_date = (clone $static_time)->modify('+ 1 days');

    $time = \Drupal::time();
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

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
      'meta' => 'Competitions and award ceremonies',
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Check that we don't have blocks if fields are empty.
    $this->assertSession()->elementNotExists('css', '#event-registration-block');
    $this->assertSession()->elementNotExists('css', '#event-contacts');

    // Assert details.
    $details_content = $this->assertSession()->elementExists('css', '#event-details');
    $this->assertSession()->elementNotExists('css', '.ecl-body', $details_content);
    $details_list_content = $this->assertSession()->elementExists('css', '.ecl-col-12.ecl-col-md-6.ecl-u-mt-l.ecl-u-mt-md-none ul.ecl-unordered-list.ecl-unordered-list--no-bullet', $details_content);
    $start_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $icons_text_assert = new IconsTextAssert();
    $icons_text_expected_values = [
      'items' => [
        [
          'icon' => 'file',
          'text' => 'Financing',
        ], [
          'icon' => 'calendar',
          'text' => $start_date->format('d F Y, H:i'),
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
          'body' => $start_date->format('l j F Y, H:i'),
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
    $start_date = (clone $static_time)->modify('+ 1 days');
    $end_date = (clone $static_time)->modify('+ 10 days');
    $node->set('oe_event_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $start_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $end_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $icons_text_expected_values = [
      'items' => [
        [
          'icon' => 'file',
          'text' => 'Financing',
        ], [
          'icon' => 'calendar',
          'text' => $start_date->format('d F Y, H:i') . "\n to " . $end_date->format('d F Y, H:i'),
        ],
      ],
    ];
    $icons_text_assert->assertPattern($icons_text_expected_values, $details_list_content->getOuterHtml());

    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'When',
          'body' => $start_date->format('l j F Y, H:i') . "\n to " . $end_date->format('l j F Y, H:i'),
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
    $venue_entity = $this->createVenueEntity([
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => "Event venue address",
        'postal_code' => '1001',
      ],
      'oe_capacity' => 'Venue capacity',
      'oe_room' => 'Venue room',
    ]);
    $node->set('oe_event_venue', [$venue_entity])->save();
    $this->drupalGet($node->toUrl());

    $venue_name = $venue_entity->getName();
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Where',
          'body' => "$venue_name\n\n  Event venue address, 1001 Brussels, Belgium",
        ], [
          'label' => 'When',
          'body' => $start_date->format('l j F Y, H:i') . "\n to " . $end_date->format('l j F Y, H:i'),
        ], [
          'label' => 'Languages',
          'body' => 'Estonian, French',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $practical_list_content->getOuterHtml());

    $icons_text_expected_values['items'][2] = [
      'icon' => 'location',
      'text' => 'Brussels, Belgium',
    ];
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
    // @todo: "Online description" isn't shown on the page. Why do we need it?
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
    $online_start_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $online_end_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $field_list_expected_values['items'][6] = [
      'label' => 'Online time',
      'body' => $online_start_date->format('j F Y, H:i T') . "\n to " . $online_end_date->format('j F Y, H:i T'),
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
    $this->assertRegisterButtonEnabled($registration_content);

    // Assert "Registration date" field in the past.
    $registration_start_date = (clone $static_time)->modify('- 2 months');
    $registration_end_date = (clone $static_time)->modify('- 1 months');
    $node->set('oe_event_registration_dates', [
      'value' => $registration_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $registration_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $this->assertSession()->elementNotExists('css', 'a.ecl-u-mt-2xl.ecl-link.ecl-link--cta', $registration_content);
    $registration_info_content = $this->assertSession()->elementExists('css', 'p.ecl-u-type-paragraph.ecl-u-type-color-grey-75');
    $registration_end_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $this->assertEquals('Registration period ended on ' . $registration_end_date->format('l j F Y, H:i'), $registration_info_content->getText());

    // Assert "Registration date" field when registration is in progress.
    $registration_start_date = (clone $static_time)->modify('- 10 days');
    $registration_end_date = (clone $static_time)->modify('+ 10 days');
    $node->set('oe_event_registration_dates', [
      'value' => $registration_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $registration_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $this->assertRegisterButtonEnabled($registration_content);
    $registration_end_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $this->assertEquals('Book your seat, 1 week left to register, registration will end on ' . $registration_end_date->format('j F Y, H:i'), $registration_info_content->getText());

    // Assert "Registration date" field when registration will finish today.
    $registration_start_date = (clone $static_time)->modify('- 10 days');
    $registration_end_date = (clone $static_time)->modify('+ 1 hours');
    $node->set('oe_event_registration_dates', [
      'value' => $registration_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $registration_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $this->assertRegisterButtonEnabled($registration_content);
    $registration_end_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $this->assertEquals('Book your seat, the registration will end today, ' . $registration_end_date->format('j F Y, H:i'), $registration_info_content->getText());

    // Assert "Registration date" field when registration will start today.
    $registration_start_date = (clone $static_time)->modify('+ 1 hour');
    $registration_end_date = (clone $static_time)->modify('+ 10 days');
    $node->set('oe_event_registration_dates', [
      'value' => $registration_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $registration_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $this->assertRegisterButtonDisabled($registration_content);
    $registration_start_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $this->assertEquals('Registration will open today, ' . $registration_start_date->format('j F Y, H:i') . '.', $registration_info_content->getText());

    // Assert "Registration date" field when registration will start in future.
    $registration_start_date = (clone $static_time)->modify('+ 1 day');
    $registration_end_date = (clone $static_time)->modify('+ 10 days');
    $node->set('oe_event_registration_dates', [
      'value' => $registration_start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $registration_end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $this->assertRegisterButtonDisabled($registration_content);
    $registration_start_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $registration_end_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $this->assertEquals('Registration will open in 1 day. You can register from ' . $registration_start_date->format('j F Y, H:i') . ', until ' . $registration_end_date->format('j F Y, H:i') . '.', $registration_info_content->getText());

    // Assert "Description summary", "Full text", "Featured media",
    // "Featured media legend" fields (these fields have to be filled all
    // together).
    $node->set('oe_event_description_summary', 'Event description summary');
    $node->set('body', 'Event full text');
    $node->set('oe_event_featured_media_legend', 'Event featured media legend');
    $media_image = $this->createMediaImage('event_featured_media');
    $node->set('oe_event_featured_media', [
      'target_id' => (int) $media_image->id(),
    ])->save();
    $this->drupalGet($node->toUrl());

    // @todo: text featured media pattern is used here -
    // templates/patterns/text_featured_media. TextFeaturedMediaAssert
    // should be implemented.
    // Assert "Report text" and "Summary for report" fields when event finished.
    $start_date = (clone $static_time)->modify('- 20 days');
    $end_date = (clone $static_time)->modify('- 10 days');
    $node->set('oe_event_report_summary', 'Event report summary');
    $node->set('oe_event_report_text', 'Event report text');
    $node->set('oe_event_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $description_summary = $this->assertSession()->elementExists('css', '.ecl-editor', $details_content);
    $this->assertEquals('Event report summary', $description_summary->getText());
    // @todo: Field oe_event_report_text is shown inside text featured media
    // pattern. TextFeaturedMediaAssert should be implemented.
    // Assert "Event contact" field.
    $contact_entity_general = $this->createContactEntity(['bundle' => 'oe_general']);
    $contact_entity_press = $this->createContactEntity(['bundle' => 'oe_press']);
    $node->set('oe_event_contact', [$contact_entity_general, $contact_entity_press])->save();
    $this->drupalGet($node->toUrl());

    $event_contacts_content = $this->assertSession()->elementExists('css', '#event-contacts');
    $event_contacts_header = $this->assertSession()->elementExists('css', 'h2.ecl-u-type-heading-2.ecl-u-type-color-black.ecl-u-mt-2xl.ecl-u-mt-md-3xl.ecl-u-mb-l', $event_contacts_content);
    $this->assertEquals('Contacts', $event_contacts_header->getText());

    $general_contacts_content = $this->assertSession()->elementExists('css', '.ecl-row #event-contacts-general', $event_contacts_content);
    $this->assertContactHeader($general_contacts_content, 'General contact');
    $this->assertContactEntityDetailsDisplay($general_contacts_content, $contact_entity_general->getName());

    $press_contacts_content = $this->assertSession()->elementExists('css', '.ecl-row #event-contacts-press', $event_contacts_content);
    $this->assertContactHeader($press_contacts_content, 'Press contact');
    $this->assertContactEntityDetailsDisplay($press_contacts_content, $contact_entity_press->getName());

    // Assert "Social media links" links.
    // @todo: Social media links pattern is used -
    // templates/patterns/social_media_links. SocialMediaLinksAssert should be
    // implemented.
  }

  /**
   * {@inheritdoc}
   */
  protected function createContactEntity(array $settings = []): ContactInterface {
    $name = $this->randomMachineName();

    $settings += [
      'name' => $name,
      'oe_email' => "$name@example.com",
      'oe_phone' => "$name phone",
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => "$name contact address",
        'postal_code' => '1003',
      ],
      'oe_social_media' => [
        [
          'uri' => "http://www.example.com/facebook_$name",
          'title' => "$name facebook",
          'link_type' => 'facebook',
        ], [
          'uri' => "http://www.example.com/linkedin_$name",
          'title' => "$name linkedin",
          'link_type' => 'linkedin',
        ],
      ],
    ];

    return parent::createContactEntity($settings);
  }

  /**
   * Assets enabled register button.
   *
   * @param \Behat\Mink\Element\NodeElement $parent_element
   *   Parent element.
   */
  protected function assertRegisterButtonEnabled(NodeElement $parent_element): void {
    $rendered_button = $this->assertSession()->elementExists('css', 'a.ecl-u-mt-2xl.ecl-link.ecl-link--cta', $parent_element);
    $this->assertEquals('Register here', $rendered_button->getText());
    $this->assertEquals('http://www.example.com/registation', $rendered_button->getAttribute('href'));
  }

  /**
   * Assets disabled register button.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Parent element.
   */
  protected function assertRegisterButtonDisabled(NodeElement $element): void {
    $rendered_button = $this->assertSession()->elementExists('css', 'button.ecl-button.ecl-button--call.ecl-u-mt-2xl.ecl-link.ecl-link--cta', $element);
    $this->assertEquals('Register here', $rendered_button->getText());
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
    $rendered_header = $this->assertSession()->elementExists('css', 'h3.ecl-u-type-heading-3.ecl-u-type-color-black.ecl-u-mt-none.ecl-u-mb-m.ecl-u-mb-md-l', $rendered_element);
    $this->assertEquals($title, $rendered_header->getText());
  }

  /**
   * Asserts rendering of Contact entity using Details view mode.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_elemenet
   *   Rendered element.
   * @param string $name
   *   Name of the Contact entity.
   */
  protected function assertContactEntityDetailsDisplay(NodeElement $rendered_elemenet, string $name): void {
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
          'body' => "$name phone",
        ], [
          'label' => 'Address',
          'body' => "$name contact address, 1003 Brussels, Belgium",
        ],
      ],
    ];
    $content = $this->assertSession()->elementExists('css', 'dl.ecl-description-list', $rendered_elemenet);
    $field_list_html = $content->getOuterHtml();
    $field_list_assert->assertPattern($field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('horizontal', $field_list_html);

    // Assert "Social media links" links.
    // @todo: Social media links pattern is used -
    // templates/patterns/social_media_links. SocialMediaLinksAssert should be
    // implemented.
  }

}
