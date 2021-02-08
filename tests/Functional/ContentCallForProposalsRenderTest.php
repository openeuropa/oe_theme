<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that "Call for proposals" content type renders correctly.
 */
class ContentCallForProposalsRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'datetime_testing',
    'block',
    'system',
    'path',
    'oe_theme_helper',
    'oe_theme_content_call_proposals',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Give anonymous users permission to view entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests full page of Call for proposals.
   */
  public function testProposalRendering(): void {
    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $publication_date = (clone $static_time)->modify('- 5 days');

    /** @var \Drupal\Component\Datetime\TimeInterface $datetime */
    $time = \Drupal::time();
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

    // Create a Call for proposal node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_call_proposals',
      'title' => 'Test Call for proposals node',
      'oe_publication_date' => [
        'value' => $publication_date->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
      ],
      'oe_call_proposals_model' => 'permanent',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $header_expected_values = [
      'title' => 'Test Call for proposals node',
      'meta' => 'Call for proposals',
    ];
    $assert->assertPattern($header_expected_values, $page_header->getOuterHtml());

    // Assert navigation part.
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Assert content part.
    $content = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l .ecl-col-lg-9');
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(1, $content_items);
    $this->assertContentHeader($content_items[0], 'Details', 'details');

    $field_list_assert = new FieldListAssert();
    $details_expected_values = [
      'items' => [
        [
          'label' => 'Status',
          'body' => 'N/A',
        ], [
          'label' => 'Publication date',
          'body' => '12 February 2020',
        ], [
          'label' => 'Deadline model',
          'body' => 'Permanent',
        ],
      ],
    ];
    $details_html = $content_items[0]->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);

    $selector = '//*[text() = "Status"]/following-sibling::dd[1]/div/span[@class="call-proposals-status ecl-u-text-uppercase"]';
    $this->assertSession()->elementExists('xpath', $selector);
    $this->assertEquals('N/A', $content_items[0]->find('xpath', $selector)->getText());

    // Assert Introduction field.
    $node->set('oe_summary', 'Call for proposals introduction')->save();
    $this->drupalGet($node->toUrl());

    $header_expected_values['description'] = 'Call for proposals introduction';
    $assert->assertPattern($header_expected_values, $page_header->getOuterHtml());

    // Assert Reference field.
    $node->set('oe_reference_code', 'Reference code')->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values = [
      'items' => [
        [
          'label' => 'Status',
          'body' => 'N/A',
        ], [
          'label' => 'Reference',
          'body' => 'Reference code',
        ], [
          'label' => 'Publication date',
          'body' => '12 February 2020',
        ], [
          'label' => 'Deadline model',
          'body' => 'Permanent',
        ],
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert Publication in the official journal field.
    $node->set('oe_call_proposals_journal', [
      'uri' => 'http://example.com/journal',
      'title' => 'Official Journal Reference',
    ])->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][2]['body'] = "12 February 2020\n  in\n  Official Journal Reference" . chr(194) . chr(160);
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    $journal_link_selector = '//*[text() = "Publication date"]/following-sibling::dd[1]/div';
    $publication_details = $this->assertSession()->elementExists('xpath', $journal_link_selector);
    $this->assertLinkIcon($publication_details, 'Official Journal Reference', 'http://example.com/journal');

    // Assert Opening date field.
    $opening_date = (clone $static_time)->modify('- 3 days');
    $node->set('oe_call_proposals_opening_date', ['value' => $opening_date->format('Y-m-d')]);
    $node->set('oe_call_proposals_journal', NULL)->save();
    $this->drupalGet($node->toUrl());

    $header_expected_values['meta'] = 'Call for proposals | Open';
    $assert->assertPattern($header_expected_values, $page_header->getOuterHtml());

    $details_expected_values = [
      'items' => [
        [
          'label' => 'Status',
          'body' => 'Open',
        ], [
          'label' => 'Reference',
          'body' => 'Reference code',
        ], [
          'label' => 'Publication date',
          'body' => '12 February 2020',
        ], [
          'label' => 'Opening date',
          'body' => '14 February 2020',
        ], [
          'label' => 'Deadline model',
          'body' => 'Permanent',
        ],
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert Deadline model and Deadline dates fields.
    $deadline_date1 = (clone $static_time)->modify('- 3 days');
    $node->set('oe_call_proposals_model', 'single_stage');
    $node->set('oe_call_proposals_deadline', $deadline_date1->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT));
    $node->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][0] = [
      'label' => 'Status',
      'body' => 'Closed',
    ];
    $details_expected_values['items'][4] = [
      'label' => 'Deadline model',
      'body' => 'Single-stage',
    ];
    $details_expected_values['items'][5] = [
      'label' => 'Deadline date',
      'body' => '15 February 2020, 01:00 (AEDT)',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert Deadline model and multiple Deadline date fields.
    $deadline_date1 = (clone $static_time)->modify('- 3 days');
    $deadline_date2 = (clone $static_time)->modify('+ 5 days');
    $node->set('oe_call_proposals_model', 'two_stage');
    $node->set('oe_call_proposals_deadline', [
      $deadline_date1->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      $deadline_date2->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ])->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][0] = [
      'label' => 'Status',
      'body' => 'Open',
    ];
    $details_expected_values['items'][4] = [
      'label' => 'Deadline model',
      'body' => 'Two-stage',
    ];
    $expected_deadline_dates = '15 February 2020, 01:00 (AEDT)23 February 2020, 01:00 (AEDT)';
    if (version_compare(PHP_VERSION, '7.3') < 0) {
      $expected_deadline_dates = "15 February 2020, 01:00 (AEDT)\n23 February 2020, 01:00 (AEDT)";
    }
    $details_expected_values['items'][5] = [
      'label' => 'Deadline dates',
      'body' => $expected_deadline_dates,
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert external Grants awarded link field.
    $node->set('oe_call_proposals_grants', ['uri' => 'http://example.com/results']);
    $node->save();
    $this->drupalGet($node->toUrl());

    $results_field_group = $content_items[0]->find('css', 'div.ecl-u-border-top.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mt-l.ecl-u-mb-l.ecl-u-pt-l.ecl-u-pb-l');
    $results_expected_values = [
      'items' => [
        [
          'label' => 'Results',
          'body' => 'Grands awarded ',
        ],
      ],
    ];
    $field_list_assert->assertPattern($results_expected_values, $results_field_group->getHtml());
    $this->assertLinkIcon($results_field_group, 'Grands awarded', 'http://example.com/results');

    // Assert internal Grants awarded link field.
    $node->set('oe_call_proposals_grants', ['uri' => 'internal:/']);
    $node->save();
    $this->drupalGet($node->toUrl());

    $field_list_assert->assertPattern($results_expected_values, $results_field_group->getHtml());
    $this->assertLinkIcon($results_field_group, 'Grands awarded', '/build/', FALSE);

    // Assert Funding programme field.
    $node->set('oe_call_proposals_funding', 'http://publications.europa.eu/resource/authority/eu-programme/AFIS2020');
    $node->save();
    $this->drupalGet($node->toUrl());

    $info_field_group = $content_items[0]->find('css', 'div.ecl-u-mt-m');
    $info_expected_values = [
      'items' => [
        [
          'label' => 'Funding programme',
          'body' => 'Anti Fraud Information System (AFIS)',
        ],
      ],
    ];
    $field_list_assert->assertPattern($info_expected_values, $info_field_group->getHtml());

    // Assert Responsible department field.
    $node->set('oe_departments', 'http://publications.europa.eu/resource/authority/corporate-body/ABEC');
    $node->save();
    $this->drupalGet($node->toUrl());

    $info_expected_values['items'][] = [
      'label' => 'Department',
      'body' => 'Audit Board of the European Communities',
    ];
    $field_list_assert->assertPattern($info_expected_values, $info_field_group->getHtml());

    // Assert multiple Responsible department field.
    $node->set('oe_departments', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $info_expected_values['items'][1] = [
      'label' => 'Departments',
      'body' => 'Audit Board of the European Communities | Associated African States and Madagascar',
    ];
    $field_list_assert->assertPattern($info_expected_values, $info_field_group->getHtml());

    // Assert Body field.
    $node->set('body', 'Call for proposals body')->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
      ['label' => 'Description', 'href' => '#description'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);
    $this->assertContentHeader($content_items[1], 'Description', 'description');
    $content_second_group = $content_items[1]->find('css', '.ecl-editor p');
    $this->assertEquals('Call for proposals body', $content_second_group->getText());

    // Assert Documents field.
    $media_document = $this->createMediaDocument('call_for_proposals_document');
    $node->set('oe_documents', [
      ['target_id' => (int) $media_document->id()],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Documents',
      'href' => '#documents',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    $this->assertContentHeader($content_items[2], 'Documents', 'documents');
    $this->assertMediaDocumentDefaultRender($content_items['2'], 'call_for_proposals_document');

    // Assert Contact field.
    $contact = $this->createContactEntity('call_proposal_contact');
    $node->set('oe_call_proposals_contact', [$contact]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Contact',
      'href' => '#contact',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(4, $content_items);
    $this->assertContentHeader($content_items[3], 'Contact', 'contact');
    $this->assertContactDefaultRender($content_items[3], 'call_proposal_contact');
  }

}
