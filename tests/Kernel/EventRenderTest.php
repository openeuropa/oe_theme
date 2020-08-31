<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\oe_content_entity_venue\Entity\Venue;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;

/**
 * Tests the event rendering.
 */
class EventRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'field_group',
    'datetime_range',
    'entity_reference_revisions',
    'link',
    'image',
    'inline_entity_form',
    'oe_content_social_media_links_field',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
    'oe_content_event',
    'composite_reference',
    'oe_theme_content_event',
    'options',
    'oe_time_caching',
    'datetime_testing',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('oe_contact');
    $this->installEntitySchema('oe_organisation');
    $this->installEntitySchema('oe_venue');
    $this->installConfig([
      'oe_content_social_media_links_field',
      'oe_content_event',
      'oe_content_entity_venue',
      'oe_theme_content_event',
      'address',
    ]);

    module_load_include('install', 'oe_theme_content_event');
    oe_theme_content_event_install();

    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Test an event being rendered as a teaser.
   */
  public function testEventTeaser(): void {
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media = Media::create([
      'bundle' => 'image',
      'name' => 'test image',
      'oe_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt',
      ],
    ]);
    $media->save();

    $venue = Venue::create([
      'name' => 'My venue',
      'bundle' => 'oe_default',
    ]);

    $venue->set('oe_address', [
      'country_code' => 'BE',
      'locality' => 'Brussels',
      'postal_code' => 1000,
      'address_line1' => 'The street',
    ]);

    $venue->save();

    $date = new \DateTime('2022-01-02');
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_organiser_name' => 'Organisation',
      'oe_event_organiser_internal' => 'http://publications.europa.eu/resource/authority/corporate-body/DIGIT',
      'oe_event_type' => 'http://publications.europa.eu/resource/authority/public-event-type/COMPETITION_AWARD_CEREMONY',
      'oe_subject' => 'http://data.europa.eu/uxp/1005',
      'oe_event_languages' => 'http://publications.europa.eu/resource/authority/language/BUL',
      'oe_event_dates' => [
        'value' => $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'end_value' => $date->modify('+ 2 days')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'timezone' => 'Europe\Brussels',
      ],
      'oe_event_status' => 'as_planned',
      'oe_event_featured_media' => $media->id(),
      'oe_event_venue' => [
        'target_id' => $venue->id(),
        'target_revision_id' => $venue->getRevisionId(),
      ],
      'oe_teaser' => 'The teaser text',
      'status' => 1,
    ];

    $node = Node::create($values);
    $node->save();

    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    /** @var \Drupal\Component\Datetime\TimeInterface $datetime */
    $time = $this->container->get('datetime.time');
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $assert = new ListItemAssert();

    $expected_values = [
      'title' => 'My node title',
      'url' => '/node/1',
      'description' => [
        'items' => [
          [
            'icon' => 'location',
            'text' => 'Brussels, Belgium',
          ],
        ],
      ],
      'meta' => 'Competitions and award ceremonies',
      'image' => NULL,
      'date' => [
        'day' => '02-04',
        'month' => '01',
        'month_name' => 'Jan',
        'year' => '2022',
      ],
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date', $html);

    // Move the current date so the event is ongoing and rebuild the teaser.
    $static_time = new DrupalDateTime('2022-01-03 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $time->setTime($static_time->getTimestamp());
    $this->nodeViewBuilder->resetCache();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = 'Competitions and award ceremonies';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date_ongoing', $html);

    // Move the current date so the event is in the past and rebuild the teaser.
    $static_time = new DrupalDateTime('2030-01-03 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $time->setTime($static_time->getTimestamp());
    $this->nodeViewBuilder->resetCache();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = 'Competitions and award ceremonies';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date_past', $html);

    // Set status as cancelled and rebuild the teaser.
    $node->set('oe_event_status', 'cancelled')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = 'Competitions and award ceremonies | Cancelled';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date_cancelled', $html);
  }

}
