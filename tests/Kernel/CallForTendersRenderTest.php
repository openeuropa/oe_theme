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
use Drupal\user\Entity\User;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests call for tenders rendering.
 *
 * @group batch2
 */
class CallForTendersRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field_group',
    'composite_reference',
    'oe_time_caching',
    'oe_content_reference_code_field',
    'oe_content_departments_field',
    'oe_content_call_tenders',
    'oe_theme_content_call_tenders',
    'datetime_testing',
    'file_link',
    'options',
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
      'oe_content_call_tenders',
      'oe_theme_content_call_tenders',
    ]);

    module_load_include('install', 'oe_content');
    oe_content_install();

    // Set current user to UID 1, so that by default we can access everything.
    $account = User::load(1);
    $this->setCurrentUser($account);
  }

  /**
   * Test a call for tenders being rendered as a teaser.
   */
  public function testTeaser(): void {
    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);

    $publication_date = (clone $static_time)->modify('- 5 days');
    $opening_date = (clone $static_time)->modify('- 3 days');
    $deadline_date = (clone $static_time)->modify('+ 3 days');

    /** @var \Drupal\Component\Datetime\TimeInterface $datetime */
    $time = $this->container->get('datetime.time');
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

    // Create a Call for tenders node.
    /** @var \Drupal\node\Entity\Node $node */
    $values = [
      'type' => 'oe_call_tenders',
      'title' => 'Test Call for tenders node',
      'oe_publication_date' => [
        'value' => $publication_date->format('Y-m-d'),
      ],
      'oe_call_tenders_opening_date' => [
        'value' => $opening_date->format('Y-m-d'),
      ],
      'oe_call_tenders_deadline' => [
        'value' => $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ],
      'oe_reference_code' => 'Call for tenders reference',
      'oe_departments' => ['http://publications.europa.eu/resource/authority/corporate-body/ABEC', 'http://publications.europa.eu/resource/authority/corporate-body/ACM'],
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
      'title' => 'Test Call for tenders node',
      'meta' => 'Call status: Open',
      'image' => NULL,
      'additional_information' => [
        new PatternAssertState(new FieldListAssert(), [
          'items' => [
            [
              'label' => 'Reference',
              'body' => 'Call for tenders reference',
            ], [
              'label' => 'Opening date',
              'body' => $opening_date->format('d F Y'),
            ], [
              'label' => 'Deadline date',
              'body' => $deadline_date->format('d F Y, H:i (T)'),
            ], [
              'label' => 'Departments',
              'body' => 'Audit Board of the European Communities | Arab Common Market',
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
    $node->set('oe_content_short_title', 'CFT short title')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['title'] = 'CFT short title';
    $assert->assertPattern($expected_values, $html);

    // Check Department/s label for multiple department values.
    $node->set('oe_departments', 'http://publications.europa.eu/resource/authority/corporate-body/ABEC')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for tenders reference',
          ], [
            'label' => 'Opening date',
            'body' => $opening_date->format('d F Y'),
          ], [
            'label' => 'Deadline date',
            'body' => $deadline_date->format('d F Y, H:i (T)'),
          ], [
            'label' => 'Department',
            'body' => 'Audit Board of the European Communities',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $html);

    // Check status Closed label and background.
    $deadline_date = (clone $static_time)->modify('- 2 days');
    $node->set('oe_call_tenders_deadline', $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT))->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = 'Call status: Closed';
    $deadline_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for tenders reference',
          ], [
            'label' => 'Opening date',
            'body' => $opening_date->format('d F Y'),
          ], [
            'label' => 'Deadline date',
            'body' => $deadline_date->format('d F Y, H:i (T)'),
          ], [
            'label' => 'Department',
            'body' => 'Audit Board of the European Communities',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $html);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('span.call-status.ecl-label.ecl-u-text-uppercase.ecl-label--low.ecl-u-type-color-black');
    $this->assertCount(1, $actual);

    // Check Deadline date is striked when Call for tenders is closed.
    $actual = $crawler->filter('dd.ecl-description-list__definition > .ecl-u-type-strike');
    $this->assertCount(1, $actual);

    // Check status Upcoming label and background.
    $opening_date = (clone $static_time)->modify('+ 10 days');
    $deadline_date = (clone $static_time)->modify('+ 5 days');
    $node->set('oe_call_tenders_opening_date', $opening_date->format('Y-m-d'))->save();
    $node->set('oe_call_tenders_deadline', $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT))->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $deadline_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $expected_values['meta'] = 'Call status: Upcoming';
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for tenders reference',
          ], [
            'label' => 'Opening date',
            'body' => $opening_date->format('d F Y'),
          ], [
            'label' => 'Deadline date',
            'body' => $deadline_date->format('d F Y, H:i (T)'),
          ], [
            'label' => 'Department',
            'body' => 'Audit Board of the European Communities',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $html);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('span.call-status.ecl-label.ecl-u-text-uppercase.ecl-label--medium.ecl-u-type-color-black');
    $this->assertCount(1, $actual);

    // Check status N/A.
    $publication_date = (clone $static_time)->modify('+ 5 days');
    $deadline_date = (clone $static_time)->modify('+ 5 days');

    $node->set('oe_publication_date', $publication_date->format('Y-m-d'))->save();
    $node->set('oe_call_tenders_opening_date', '')->save();
    $node->set('oe_call_tenders_deadline', $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT))->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $deadline_date->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $expected_values['meta'] = '';
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for tenders reference',
          ], [
            'label' => 'Deadline date',
            'body' => $deadline_date->format('d F Y, H:i (T)'),
          ], [
            'label' => 'Department',
            'body' => 'Audit Board of the European Communities',
          ],
        ],
      ]),
    ];

    $assert->assertPattern($expected_values, $html);
  }

}
