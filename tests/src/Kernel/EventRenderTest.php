<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\oe_content_entity_venue\Entity\Venue;
use Drupal\Tests\oe_theme\PatternAssertions\IconsTextAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternAssertState;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests the event rendering.
 *
 * @group batch3
 */
class EventRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'datetime_range',
    'datetime_range_timezone',
    'entity_reference_revisions',
    'link',
    'image',
    'inline_entity_form',
    'oe_content_social_media_links_field',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
    'oe_content_event_event_programme',
    'oe_content_event',
    'composite_reference',
    'oe_theme_content_event',
    'options',
    'oe_time_caching',
    'datetime_testing',
    'file_link',
    'options',
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

    \Drupal::moduleHandler()->loadInclude('oe_content', 'install');
    oe_content_install(FALSE);

    // Set current user to UID 1, so that by default we can access everything.
    $account = User::load(1);
    $this->setCurrentUser($account);
  }

  /**
   * Test an event being rendered as a teaser.
   */
  public function testEventTeaser(): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
    $file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
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
    ]);

    $venue->save();

    // Make node title translatable.
    $fields = \Drupal::service('entity_field.manager')
      ->getBaseFieldDefinitions('node', 'oe_event');
    $field_config = $fields['title']->getConfig('oe_event');
    $field_config->setTranslatable(TRUE);
    $field_config->save();

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

    // Translate the event into bulgarian.
    $values['title'] = "заглавието на моя възел";
    $values['oe_teaser'] = "Текстът на тийзъра";
    $node->addTranslation('bg', $values);
    $node->save();

    // Translate the date strings into bulgarian.
    $this->translateLocaleString('Jan', 'Ян.', 'bg');

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
      'url' => '/en/node/1',
      'description' => new PatternAssertState(new IconsTextAssert(), [
        'items' => [
          [
            'icon' => 'location',
            'text' => 'Belgium',
            'size' => 'xs',
          ],
        ],
      ]),
      'badges' => NULL,
      'meta' => ['Competitions and award ceremonies'],
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

    // Test short title fallback and highlighted label.
    $node->set('oe_content_short_title', 'Event short title');
    $node->set('sticky', NodeInterface::STICKY)->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['title'] = 'Event short title';
    $expected_values['badges'] = [
      [
        'label' => 'Highlighted',
        'variant' => 'highlight',
      ],
    ];
    $assert->assertPattern($expected_values, $html);

    // Set full address in venue.
    $venue->set('oe_address', [
      'country_code' => 'BE',
      'locality' => '<Brussels>',
      'postal_code' => 1000,
      'address_line1' => 'The street',
    ])->save();

    // Set the online type to be livestream and assert the details are updated.
    $node->set('oe_event_online_type', 'livestream')->save();
    $this->nodeViewBuilder->resetCache();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['description'] = new PatternAssertState(new IconsTextAssert(), [
      'items' => [
        [
          'icon' => 'location',
          'text' => '<Brussels>, Belgium',
          'size' => 'xs',
        ],
        [
          'icon' => 'livestreaming',
          'text' => 'Live streaming available',
          'size' => 'xs',
        ],
      ],
    ]);
    $assert->assertPattern($expected_values, $html);

    // Mark event as online only assert location is replaced.
    $node->set('oe_event_online_only', TRUE)->save();
    $this->nodeViewBuilder->resetCache();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['description'] = new PatternAssertState(new IconsTextAssert(), [
      'items' => [
        [
          'icon' => 'location',
          'text' => 'Online only',
          'size' => 'xs',
        ],
        [
          'icon' => 'livestreaming',
          'text' => t('Live streaming available'),
          'size' => 'xs',
        ],
      ],
    ]);
    $assert->assertPattern($expected_values, $html);

    // Move the current date so the event is ongoing and rebuild the teaser.
    $static_time = new DrupalDateTime('2022-01-03 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $time->setTime($static_time->getTimestamp());
    $this->nodeViewBuilder->resetCache();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = ['Competitions and award ceremonies'];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date_ongoing', $html);

    // Move the current date so the event is in the past and rebuild the teaser.
    $static_time = new DrupalDateTime('2030-01-03 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $time->setTime($static_time->getTimestamp());
    $this->nodeViewBuilder->resetCache();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = ['Competitions and award ceremonies'];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date_past', $html);

    // Set status as cancelled and rebuild the teaser.
    $node->set('oe_event_status', 'cancelled')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = [
      'Competitions and award ceremonies',
      'Cancelled',
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date_cancelled', $html);

    // Unmark event online only.
    $node->set('oe_event_online_only', FALSE)->save();

    // Assert bulgarian translation.
    $config_factory = \Drupal::configFactory();
    $config_factory->getEditable('system.site')->set('default_langcode', 'bg')->save();
    \Drupal::languageManager()->reset();
    $build = $this->nodeViewBuilder->view($node, 'teaser', 'bg');
    $html = $this->renderRoot($build);
    $expected_values['title'] = 'заглавието на моя възел';
    $expected_values['badges'] = NULL;
    $expected_values['meta'] = [
      'Конкурси и церемонии по награждаване',
      'Cancelled',
    ];
    $expected_values['url'] = '/bg/node/1';
    $expected_values['date']['month_name'] = 'Ян.';
    $expected_values['description'] = new PatternAssertState(new IconsTextAssert(), [
      'items' => [
        [
          'icon' => 'location',
          'text' => '<Brussels>, Белгия',
          'size' => 'xs',
        ],
        [
          'icon' => 'livestreaming',
          'text' => 'Live streaming available',
          'size' => 'xs',
        ],
      ],
    ]);
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('date_cancelled', $html);

    // Verify that the teaser renders correctly when a non-existing event type
    // is set.
    $node->set('oe_event_type', 'http://publications.europa.eu/resource/authority/public-event-type/OP_DATPRO')
      ->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser', 'bg');
    $html = $this->renderRoot($build);
    unset($expected_values['meta']);
    $assert->assertPattern($expected_values, $html);
  }

}
