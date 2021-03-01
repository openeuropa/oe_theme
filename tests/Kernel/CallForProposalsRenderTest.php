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
 * Tests call for proposals rendering.
 */
class CallForProposalsRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'options',
    'field_group',
    'composite_reference',
    'oe_time_caching',
    'oe_content_reference_code_field',
    'oe_content_departments_field',
    'oe_content_call_proposals',
    'oe_theme_content_call_proposals',
    'datetime_testing',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig([
      'composite_reference',
      'oe_content_reference_code_field',
      'oe_content_departments_field',
      'oe_content_call_proposals',
      'oe_theme_content_call_proposals',
    ]);

    module_load_include('install', 'oe_content');
    oe_content_install();

    $this->setUpCurrentUser([], [], TRUE);
  }

  /**
   * Test a call for proposals being rendered as a teaser.
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

    // Create a Call for proposals node.
    /** @var \Drupal\node\Entity\Node $node */
    $values = [
      'type' => 'oe_call_proposals',
      'title' => 'Test Call for proposals node',
      'oe_publication_date' => [
        'value' => $publication_date->format('Y-m-d'),
      ],
      'oe_call_proposals_opening_date' => [
        'value' => $opening_date->format('Y-m-d'),
      ],
      'oe_call_proposals_deadline' => [
        'value' => $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ],
      'oe_call_proposals_model' => 'single_stage',
      'oe_call_proposals_funding' => ['http://publications.europa.eu/resource/authority/corporate-body/ACM'],
      'oe_reference_code' => 'Call for proposals reference',
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
      'title' => 'Test Call for proposals node',
      'meta' => '<span class="call-status ecl-label ecl-u-text-uppercase ecl-u-type-color-black ecl-label--high">Call status: Open</span>',
      'image' => NULL,
      'additional_information' => [
        new PatternAssertState(new FieldListAssert(), [
          'items' => [
            [
              'label' => 'Reference',
              'body' => 'Call for proposals reference',
            ], [
              'label' => 'Opening date',
              'body' => '14 February 2020',
            ], [
              'label' => 'Deadline model',
              'body' => 'Single-stage',
            ], [
              'label' => 'Deadline date',
              'body' => '21 February 2020',
            ], [
              'label' => 'Funding programme',
              'body' => 'Arab Common Market',
            ],
          ],
        ]),
      ],
    ];
    $assert->assertPattern($expected_values, $html);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('span.call-status.ecl-label.ecl-u-text-uppercase.ecl-label--high.ecl-u-type-color-black');
    $this->assertCount(1, $actual);

    // Check label for multiple deadline values.
    $deadline_date2 = (clone $static_time)->modify('+ 4 days');
    $deadline_date2->setTimeZone(new \DateTimeZone('Australia/Sydney'));
    $node->set('oe_call_proposals_deadline', [
      $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      $deadline_date2->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->set('oe_call_proposals_model', 'two_stage')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for proposals reference',
          ], [
            'label' => 'Opening date',
            'body' => '14 February 2020',
          ], [
            'label' => 'Deadline model',
            'body' => 'Two-stage',
          ], [
            'label' => 'Deadline dates',
            'body' => "21 February 2020\n | 22 February 2020",
          ], [
            'label' => 'Funding programme',
            'body' => 'Arab Common Market',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $html);

    // Check status Closed label and background.
    $deadline_date->modify('- 4 days');
    $node->set('oe_call_proposals_deadline', [
      $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->set('oe_call_proposals_model', 'multiple_cut_off')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = '<span class="call-status ecl-label ecl-u-text-uppercase ecl-u-type-color-black ecl-label--low">Call status: Closed</span>';
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for proposals reference',
          ], [
            'label' => 'Opening date',
            'body' => '14 February 2020',
          ], [
            'label' => 'Deadline model',
            'body' => 'Multiple cut-off',
          ], [
            'label' => 'Deadline date',
            'body' => '17 February 2020',
          ], [
            'label' => 'Funding programme',
            'body' => 'Arab Common Market',
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
    $node->set('oe_call_proposals_deadline', [
      $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ]);
    $node->set('oe_call_proposals_model', 'single_stage')->save();
    $node->set('oe_call_proposals_opening_date', $opening_date->format('Y-m-d'))->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = '<span class="call-status ecl-label ecl-u-text-uppercase ecl-u-type-color-black ecl-label--medium">Call status: Upcoming</span>';
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for proposals reference',
          ], [
            'label' => 'Opening date',
            'body' => '24 February 2020',
          ], [
            'label' => 'Deadline model',
            'body' => 'Single-stage',
          ], [
            'label' => 'Deadline date',
            'body' => '21 February 2020',
          ], [
            'label' => 'Funding programme',
            'body' => 'Arab Common Market',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $html);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('span.call-status.ecl-label.ecl-u-text-uppercase.ecl-label--medium.ecl-u-type-color-black');
    $this->assertCount(1, $actual);

    // Check status N/A.
    $publication_date->modify('+ 5 days');
    $deadline_date->modify('+ 5 days');

    $node->set('oe_publication_date', $publication_date->format('Y-m-d'));
    $node->set('oe_call_proposals_opening_date', '');
    $node->set('oe_call_proposals_deadline', $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT));
    $node->set('oe_call_proposals_model', 'permanent')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['meta'] = '';
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Reference',
            'body' => 'Call for proposals reference',
          ], [
            'label' => 'Deadline model',
            'body' => 'Permanent',
          ], [
            'label' => 'Funding programme',
            'body' => 'Arab Common Market',
          ],
        ],
      ]),
    ];

    $assert->assertPattern($expected_values, $html);
  }

}
