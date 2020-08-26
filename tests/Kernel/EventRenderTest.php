<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

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
    'oe_time_caching'
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
        'end_value' => $date->modify('+ 2 hours')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
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

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $assert = new ListItemAssert();
    $assert->assertVariant('date', $html);
    $assert->assertPattern([
      'title' => '',
      'description' => '',
      'meta' => [],
      // If NULL, it should assert that there is no value.
      'image' => NULL
    ], $html);
  }

}
