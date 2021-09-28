<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternAssertState;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests consultation rendering.
 *
 * @group batch2
 */
class ConsultationRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'options',
    'field_group',
    'composite_reference',
    'oe_time_caching',
    'oe_content_departments_field',
    'oe_content_consultation',
    'oe_content_sub_entity_document_reference',
    'oe_theme_content_consultation',
    'datetime_testing',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig([
      'composite_reference',
      'oe_content_departments_field',
      'oe_content_consultation',
      'oe_theme_content_consultation',
    ]);

    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);

    $this->setUpCurrentUser([], [], TRUE);
  }

  /**
   * Test a consultation being rendered as a teaser.
   */
  public function testTeaser(): void {
    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);

    $opening_date = (clone $static_time)->modify('- 3 days');
    $deadline_date = (clone $static_time)->modify('+ 3 days');

    /** @var \Drupal\Component\Datetime\TimeInterface $datetime */
    $time = $this->container->get('datetime.time');
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

    // Create a Consultation node.
    /** @var \Drupal\node\Entity\Node $node */
    $values = [
      'type' => 'oe_consultation',
      'title' => 'Test Consultation node',
      'oe_consultation_opening_date' => [
        'value' => $opening_date->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
      ],
      'oe_consultation_deadline' => [
        'value' => $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ],
      'uid' => 0,
      'status' => 1,
    ];
    $node = Node::create($values);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    // Check Open status label and background.
    $assert = new ListItemAssert();
    $deadline_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $expected_values = [
      'title' => 'Test Consultation node',
      'meta' => 'Status: Open',
      'image' => NULL,
      'additional_information' => [
        new PatternAssertState(new FieldListAssert(), [
          'items' => [
            [
              'label' => 'Opening date',
              'body' => '14 February 2020',
            ],
            [
              'label' => 'Deadline',
              'body' => '21 February 2020, 01:00 (AEDT)',
            ],
          ],
        ]),
      ],
    ];
    $assert->assertPattern($expected_values, $html);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('span.call-status.ecl-label.ecl-u-text-uppercase.ecl-label--high.ecl-u-type-color-black');
    $this->assertCount(1, $actual);

    // Test short title fallback.
    $node->set('oe_content_short_title', 'Consultation short title')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['title'] = 'Consultation short title';
    $assert->assertPattern($expected_values, $html);

    // Check status Closed label and background.
    $deadline_date->modify('- 4 days');
    $node->set('oe_consultation_deadline', [
      $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = 'Status: Closed';
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Opening date',
            'body' => '14 February 2020',
          ], [
            'label' => 'Deadline',
            'body' => '17 February 2020, 12:00 (AEDT)',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $html);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('span.call-status.ecl-label.ecl-u-text-uppercase.ecl-label--low.ecl-u-type-color-black');
    $this->assertCount(1, $actual);

    // Check status Upcoming label and background.
    $opening_date->modify('+ 10 days');
    $deadline_date->modify('+ 4 days');
    $node->set('oe_consultation_deadline', [
      $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->set('oe_consultation_opening_date', $opening_date->format(DateTimeItemInterface::DATE_STORAGE_FORMAT))->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = 'Status: Upcoming';
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Opening date',
            'body' => '24 February 2020',
          ], [
            'label' => 'Deadline',
            'body' => '21 February 2020, 12:00 (AEDT)',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $html);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('span.call-status.ecl-label.ecl-u-text-uppercase.ecl-label--medium.ecl-u-type-color-black');
    $this->assertCount(1, $actual);
  }

}
