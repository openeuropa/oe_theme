<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;

/**
 * Tests that "Call for tenders" content type renders correctly.
 *
 * @group batch1
 */
class ContentCallForTendersRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'block',
    'system',
    'path',
    'oe_theme_helper',
    'oe_theme_content_call_tenders',
    'datetime_testing',
  ];

  /**
   * Tests that the Call for tenders page renders correctly.
   */
  public function testTenderRendering(): void {
    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);

    $publication_date = (clone $static_time)->modify('- 5 days');
    $opening_date = (clone $static_time)->modify('- 3 days');
    $deadline_date = (clone $static_time)->modify('+ 3 days');

    /** @var \Drupal\Component\Datetime\TimeInterface $datetime */
    $time = \Drupal::time();
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

    // Create a Call for tender node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_call_tenders',
      'title' => 'Test Call for tenders node',
      'oe_publication_date' => [
        'value' => $publication_date->format('Y-m-d'),
      ],
      'oe_call_tenders_deadline' => [
        'value' => $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
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
      'title' => 'Test Call for tenders node',
      'meta' => 'Call for tenders',
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

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
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(1, $content_items);
    $this->assertContentHeader($content_items[0], 'Details', 'details');

    $field_list_assert = new FieldListAssert();
    $details_expected_values = [];
    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'N/A',
      ], [
        'label' => 'Publication date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Deadline date',
        'body' => '21 February 2020, 01:00 (AEDT)',
      ],
    ];
    $details_html = $content_items[0]->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);
    $this->assertDeadlineDateStrike($content);

    // Assert Introduction field.
    $node->set('oe_summary', 'Call for tenders introduction')->save();
    $this->drupalGet($node->toUrl());
    $page_header_expected_values = [
      'title' => 'Test Call for tenders node',
      'description' => 'Call for tenders introduction',
      'meta' => 'Call for tenders',
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert "Open" status.
    $node->set('oe_call_tenders_opening_date', ['value' => $opening_date->format('Y-m-d')])->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = 'Call for tenders | Open';
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Open',
      ], [
        'label' => 'Publication date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Opening date',
        'body' => '14 February 2020',
      ], [
        'label' => 'Deadline date',
        'body' => '21 February 2020, 01:00 (AEDT)',
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());
    $this->assertDeadlineDateStrike($content);

    // Assert "Upcoming" status.
    $opening_date = (clone $static_time)->modify('+ 2 days');
    $node->set('oe_call_tenders_opening_date', ['value' => $opening_date->format('Y-m-d')])->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = 'Call for tenders | Upcoming';
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Upcoming',
      ], [
        'label' => 'Publication date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Opening date',
        'body' => '19 February 2020',
      ], [
        'label' => 'Deadline date',
        'body' => '21 February 2020, 01:00 (AEDT)',
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());
    $this->assertDeadlineDateStrike($content);

    // Assert "Closed" status.
    $deadline_date = (clone $static_time)->modify('- 2 days');
    $node->set('oe_call_tenders_deadline', $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT))->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = 'Call for tenders | Closed';
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Closed',
      ], [
        'label' => 'Publication date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Opening date',
        'body' => '19 February 2020',
      ], [
        'label' => 'Deadline date',
        'body' => '16 February 2020, 01:00 (AEDT)',
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());
    $this->assertDeadlineDateStrike($content, TRUE);

    // Assert Reference field.
    $node->set('oe_reference_code', 'Call for tenders reference')->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Closed',
      ], [
        'label' => 'Reference',
        'body' => 'Call for tenders reference',
      ], [
        'label' => 'Publication date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Opening date',
        'body' => '19 February 2020',
      ], [
        'label' => 'Deadline date',
        'body' => '16 February 2020, 01:00 (AEDT)',
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert Responsible department field.
    $node->set('oe_departments', 'http://publications.europa.eu/resource/authority/corporate-body/ABEC')->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][5] = [
      'label' => 'Department',
      'body' => 'Audit Board of the European Communities',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert multiple Responsible department field.
    $node->set('oe_departments', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][5] = [
      'label' => 'Departments',
      'body' => 'Audit Board of the European Communities | Associated African States and Madagascar',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert Body text field.
    $node->set('body', 'Call for tenders body');
    $node->save();
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
    $this->assertEquals('Call for tenders body', $content_second_group->getText());

    // Assert Documents field.
    $media_document = $this->createMediaDocument('call_for_tenders_document');
    $node->set('oe_documents', [
      ['target_id' => (int) $media_document->id()],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
      ['label' => 'Description', 'href' => '#description'],
      ['label' => 'Documents', 'href' => '#documents'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    $this->assertContentHeader($content_items[2], 'Documents', 'documents');
    $this->assertMediaDocumentDefaultRender($content_items['2'], 'call_for_tenders_document');
  }

  /**
   * Asserts deadline data field value.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param bool $is_strike
   *   Whether value should be strike or not.
   */
  protected function assertDeadlineDateStrike(NodeElement $element, bool $is_strike = FALSE): void {
    $value_wrapper_element = $element->find('xpath', '//*[text() = "Deadline date"]/following-sibling::dd/div');
    if ($is_strike) {
      $this->assertTrue($value_wrapper_element->hasClass('ecl-u-type-strike'));
    }
    else {
      $this->assertFalse($value_wrapper_element->hasClass('ecl-u-type-strike'));
    }
  }

}
