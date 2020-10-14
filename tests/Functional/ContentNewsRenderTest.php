<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our News content type renders correctly.
 */
class ContentNewsRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_news',
    'oe_theme_content_entity_contact',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the News page renders correctly.
   */
  public function testNewsRendering(): void {
    // Create a News node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_teaser' => 'News teaser',
      'oe_summary' => 'News summary',
      'body' => 'Body',
      'oe_news_types' => [
        'http://publications.europa.eu/resource/authority/resource-type/FACTSHEET',
        'http://publications.europa.eu/resource/authority/resource-type/PUB_GEN',
      ],
      'oe_reference_code' => 'News reference',
      'oe_publication_date' => [
        'value' => '2020-09-18',
      ],
      'oe_author' => [
        'http://publications.europa.eu/resource/authority/corporate-body/ACJHR',
        'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      ],
      'oe_news_location' => 'http://publications.europa.eu/resource/authority/place/ARE_AUH',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert news contacts.
    $this->assertContactEntityDefaultDisplay($node, 'oe_news_contacts');

    // Assert news contacts header.
    $contacts = $this->assertSession()->elementExists('css', 'div#news-contacts');
    $this->assertContentHeader($contacts, 'Contacts');

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $expected_values = [
      'title' => 'Test news node',
      'description' => 'News summary',
      'meta' => 'Factsheet, General publications | 18 September 2020 | Abu Dhabi | African Court of Justice and Human Rights, Arab Common Market',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Assert news details.
    $details = $this->assertSession()->elementExists('css', 'div#news-details');
    $field_list_assert = new FieldListAssert();
    $details_expected_values = [
      'items' => [
        [
          'label' => 'Reference',
          'body' => 'News reference',
        ],
        [
          'label' => 'Publication date',
          'body' => '18 September 2020',
        ],
        [
          'label' => 'Authors',
          'body' => 'African Court of Justice and Human Rights | Arab Common Market',
        ],
        [
          'label' => 'Location',
          'body' => 'Abu Dhabi',
        ],
      ],
    ];
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);

    // Assert Author field label.
    $node->set('oe_author', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ACP_CDE'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $details_expected_values['items'][2]['label'] = 'Authors';
    $details_expected_values['items'][2]['body'] = 'African Court of Justice and Human Rights | Centre for the Development of Enterprise';
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
  }

}
