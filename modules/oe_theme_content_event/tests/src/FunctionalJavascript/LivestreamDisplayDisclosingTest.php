<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_content_event\FunctionalJavascript;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Test livestream description and link disclosing.
 *
 * @group batch3
 *
 * @group oe_theme_content_event
 */
class LivestreamDisplayDisclosingTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'path',
    'block',
    'datetime_testing',
    'oe_theme_helper',
    'oe_theme_content_event',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

    FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
    ])->save();
  }

  /**
   * Tests that Event livestreaming link will be visible exactly on time.
   */
  public function testLivestreamDisplayDisclosing(): void {
    $static_time = new DrupalDateTime('now', DateTimeItemInterface::STORAGE_TIMEZONE);
    $start_date = (clone $static_time)->modify('+10 days');
    // Create an Event node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->create([
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
        'oe_event_online_dates' => [
          'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
          'end_value' => $start_date->modify('+3 hours')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
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
    $livetime_elements = $this->getSession()->getPage()->findAll('css', '[data-livestream-element]');
    $this->assertCount(0, $livetime_elements);
    $livestream_js = $this->xpath("//script[contains(@src, 'js/event_livestream.js')]");
    $this->assertCount(0, $livestream_js);

    // Set livestream start date in 10 minutes later.
    $start_date = (clone $static_time)->modify('+10 minutes');
    $node->set('oe_event_online_type', 'livestream');
    $node->set('oe_event_online_link', [
      'uri' => 'http://www.example.com/online_link',
      'title' => 'Link to online event',
    ]);
    $node->set('oe_event_online_description', 'Online event description');
    $node->set('oe_event_online_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $start_date->modify('+3 hours')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $livestream_js = $this->xpath("//script[contains(@src, 'js/event_livestream.js')]");
    $this->assertCount(1, $livestream_js);
    $livetime_elements = $this->getSession()->getPage()->findAll('css', '[data-livestream-element]');
    $this->assertCount(2, $livetime_elements);
    foreach ($livetime_elements as $livetime_element) {
      $this->assertFalse($livetime_element->isVisible());
    }

    // Set livestream start date in 10 seconds later.
    $start_date = (clone $static_time)->modify('+10 seconds');
    $node->set('oe_event_online_dates', [
      'value' => $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $start_date->modify('+3 hours')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $livestream_js = $this->xpath("//script[contains(@src, 'js/event_livestream.js')]");
    $this->assertCount(1, $livestream_js);
    $livetime_elements = $this->getSession()->getPage()->findAll('css', '[data-livestream-element]');
    $this->assertCount(2, $livetime_elements);
    foreach ($livetime_elements as $livetime_element) {
      $this->assertFalse($livetime_element->isVisible());
    }
    $this->getSession()->wait(10000);
    foreach ($livetime_elements as $livetime_element) {
      $this->assertTrue($livetime_element->isVisible());
    }

    // During livestream active period and cache tag invalidation,
    // we as expected should see livestream information but don't have anymore
    // javascript for disclosing.
    $node->set('oe_event_online_dates', [
      'value' => (clone $static_time)->modify('-1 hour')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => (clone $static_time)->modify('+3 hours')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $livestream_js = $this->xpath("//script[contains(@src, 'js/event_livestream.js')]");
    $this->assertCount(0, $livestream_js);
    $livetime_elements = $this->getSession()->getPage()->findAll('css', '[data-livestream-element]');
    $this->assertCount(2, $livetime_elements);
    foreach ($livetime_elements as $livetime_element) {
      $this->assertTrue($livetime_element->isVisible());
    }

    // When livestream is over and after cache tag invalidation,
    // we should not see livestream information and don't have
    // javascript for disclosing.
    $node->set('oe_event_online_dates', [
      'value' => (clone $static_time)->modify('-4 hours')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => (clone $static_time)->modify('-1 hour')->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->drupalGet($node->toUrl());
    $livestream_js = $this->xpath("//script[contains(@src, 'js/event_livestream.js')]");
    $this->assertCount(0, $livestream_js);
    $livetime_elements = $this->getSession()->getPage()->findAll('css', '[data-livestream-element]');
    $this->assertCount(0, $livetime_elements);
  }

}
