<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the event rendering.
 */
class TenderRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_group',
    'composite_reference',
    'oe_time_caching',
    'oe_content_reference_code_field',
    'oe_content_departments_field',
    'oe_content_tender',
    'oe_theme_content_tender',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig([
      'composite_reference',
      'oe_content_departments_field',
      'oe_content_reference_code_field',
      'oe_content_tender',
      'oe_theme_content_tender',
    ]);

    module_load_include('install', 'oe_theme_content_tender');
    oe_theme_content_tender_install();

    module_load_include('install', 'oe_content');
    oe_content_install();

    // Set current user to UID 1, so that by default we can access everything.
    $account = User::load(1);
    $this->setCurrentUser($account);
  }

  /**
   * Test an event being rendered as a teaser.
   */
  public function testTenderTeaser(): void {
    // Create a Call for tender node.
    /** @var \Drupal\node\Entity\Node $node */
    $values = [
      'type' => 'oe_tender',
      'title' => 'Test Call for tenders node',
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_tender_opening_date' => [
        'value' => '2020-04-30',
      ],
      'oe_tender_deadline' => [
        'value' => date('Y') + 1 . '-06-10T23:30:00',
      ],
      'oe_reference_code' => 'Call for tenders reference',
      'oe_departments' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'uid' => 0,
      'status' => 1,
    ];
    $node = Node::create($values);
    $node->save();

    $build = $this->container->get('entity_type.manager')->getViewBuilder('node')->view($node, 'teaser');
    $html = $this->container->get('renderer')->renderRoot($build);

    // Check Open status.
    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Test Call for tenders node',
      'meta' => 'Call status: Open',
      'image' => NULL,
    ];
    $assert->assertPattern($expected_values, $html->__toString());

    $crawler = new Crawler($html->__toString());
    $actual = $crawler->filter('span.tender-status.ecl-label.ecl-u-text-uppercase.ecl-label--high');
    $this->assertCount(1, $actual);

    $assert = new FieldListAssert();
    $expected_values = [
      'items' => [
        [
          'label' => 'Reference',
          'body' => 'Call for tenders reference',
        ], [
          'label' => 'Opening date',
          'body' => '30 April 2020',
        ], [
          'label' => 'Deadline date',
          'body' => '11 June 2021, 09:30 (AEST)',
        ], [
          'label' => 'Department',
          'body' => 'Audit Board of the European Communities',
        ],
      ],
    ];
    $assert->assertPattern($expected_values, $html->__toString());
    $assert->assertVariant('horizontal', $html->__toString());

    // Check Deparment/s label for multiple deparments values.
    $node->set('oe_departments', ['http://publications.europa.eu/resource/authority/corporate-body/ABEC', 'http://publications.europa.eu/resource/authority/corporate-body/ACM'])->save();
    $build = $this->container->get('entity_type.manager')->getViewBuilder('node')->view($node, 'teaser');
    $html = $this->container->get('renderer')->renderRoot($build);

    $assert = new FieldListAssert();
    $expected_values = [
      'items' => [
        [
          'label' => 'Reference',
          'body' => 'Call for tenders reference',
        ], [
          'label' => 'Opening date',
          'body' => '30 April 2020',
        ], [
          'label' => 'Deadline date',
          'body' => '11 June 2021, 09:30 (AEST)',
        ], [
          'label' => 'Departments',
          'body' => 'Audit Board of the European CommunitiesArab Common Market',
        ],
      ],
    ];
    $assert->assertPattern($expected_values, $html->__toString());
    $assert->assertVariant('horizontal', $html->__toString());

    // Check status Closed.
    $node->set('oe_tender_deadline', date('Y') - 1 . '-06-10')->save();
    $node->save();
    $build = $this->container->get('entity_type.manager')->getViewBuilder('node')->view($node, 'teaser');
    $html = $this->container->get('renderer')->renderRoot($build);

    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Test Call for tenders node',
      'meta' => 'Call status: Closed',
      'image' => NULL,
    ];
    $assert->assertPattern($expected_values, $html->__toString());
    $crawler = new Crawler($html->__toString());
    $actual = $crawler->filter('span.tender-status.ecl-label.ecl-u-text-uppercase.ecl-label--highlight');
    $this->assertCount(1, $actual);

    // Check status Upcoming.
    $node->set('oe_tender_opening_date', date('Y') + 1 . '-06-10')->save();
    $node->set('oe_tender_deadline', date('Y') + 2 . '-06-10')->save();
    $build = $this->container->get('entity_type.manager')->getViewBuilder('node')->view($node, 'teaser');
    $html = $this->container->get('renderer')->renderRoot($build);

    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Test Call for tenders node',
      'meta' => 'Call status: Upcoming',
      'image' => NULL,
    ];
    $assert->assertPattern($expected_values, $html->__toString());
    $crawler = new Crawler($html->__toString());
    $actual = $crawler->filter('span.tender-status.ecl-label.ecl-u-text-uppercase.ecl-label--medium');
    $this->assertCount(1, $actual);
  }

}
