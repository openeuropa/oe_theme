<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

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
   * Tests that the Call for proposals page renders correctly.
   */
  public function testProposalRendering(): void {
    // Create a Call for proposal node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_call_proposals',
      'title' => 'Test Call for proposals node',
      'oe_publication_date' => [
        'value' => '2020-04-15',
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
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
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
          'body' => '15 April 2020',
        ], [
          'label' => 'Deadline model',
          'body' => 'Permanent',
        ],
      ],
    ];
    $details_html = $content_items[0]->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);

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
          'body' => '15 April 2020',
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

    $details_expected_values['items'][2]['body'] = "15 April 2020\n  in\n  Official Journal Reference" . chr(194) . chr(160);
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());
  }

}
