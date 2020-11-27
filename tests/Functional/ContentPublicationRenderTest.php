<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;

/**
 * Tests that "Publication" content type renders correctly.
 */
class ContentPublicationRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'block',
    'system',
    'path',
    'oe_theme_helper',
    'oe_theme_content_publication',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Give anonymous users permission to view published entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the Publication page renders correctly.
   */
  public function testPublicationRendering(): void {
    // Create a document for Publication.
    $media_document = $this->createMediaDocument('publication_document');

    // Create a Publication node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_publication_type' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'oe_documents' => [$media_document],
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $page_header_expected_values = [
      'title' => 'Test Publication node',
      'meta' => 'Abstract',
    ];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert navigation part.
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Files', 'href' => '#files'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Assert content part.
    $content = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l .ecl-col-lg-9');
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);

    // Assert header of first field group.
    $this->assertContentHeader($content_items[0], 'Details', 'details');

    // Assert values of first group.
    $field_list_assert = new FieldListAssert();
    $details_expected_values = [
      'items' => [
        [
          'label' => 'Publication date',
          'body' => '15 April 2020',
        ],
      ],
    ];
    $details_html = $content_items[0]->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);

    // Assert header of second field group.
    $this->assertContentHeader($content_items[1], 'Files', 'files');
    $this->assertMediaDocumentDefaultRender($content_items[1], 'publication_document');

    // Assert Introduction and multiple Resource type fields.
    $node->set('oe_summary', 'Publication introduction');
    $node->set('oe_publication_type', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/resource-type/ACT_LEGIS'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $assert = new PatternPageHeaderAssert();
    $page_header_expected_values = [
      'title' => 'Test Publication node',
      'meta' => 'Abstract | Legislative acts',
      'description' => 'Publication introduction',
    ];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert single Identifier code field.
    $node->set('oe_reference_codes', 'ID 1')->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values = [
      'items' => [
        [
          'label' => 'Identification',
          'body' => 'ID 1',
        ], [
          'label' => 'Publication date',
          'body' => '15 April 2020',
        ],
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert multiple Identifier code field.
    $node->set('oe_reference_codes', ['ID 1', 'ID 2'])->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values = [
      'items' => [
        [
          'label' => 'Identification',
          'body' => 'ID 1, ID 2',
        ], [
          'label' => 'Publication date',
          'body' => '15 April 2020',
        ],
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert Last update date field.
    $node->set('oe_publication_last_updated', '2020-06-17')->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values = [
      'items' => [
        [
          'label' => 'Identification',
          'body' => 'ID 1, ID 2',
        ], [
          'label' => 'Publication date',
          'body' => '15 April 2020 (Last updated on: 17 June 2020)',
        ],
      ],
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert single Related department field.
    $node->set('oe_departments', 'http://publications.europa.eu/resource/authority/corporate-body/ABEC');
    $node->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][] = [
      'label' => 'Related department',
      'body' => 'Audit Board of the European Communities',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert multiple Related department field.
    $node->set('oe_departments', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM'],
    ])->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][2] = [
      'label' => 'Related departments',
      'body' => 'Audit Board of the European Communities | Associated African States and Madagascar',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert single Country field.
    $node->set('oe_publication_countries', 'http://publications.europa.eu/resource/authority/country/GBR');
    $node->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][] = [
      'label' => 'Country',
      'body' => 'United Kingdom',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert multiple Country field.
    $node->set('oe_publication_countries', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/country/GBR'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/country/FRA'],
    ])->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][3] = [
      'label' => 'Countries',
      'body' => 'United Kingdom, France',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert Body text field.
    $node->set('body', 'Publication body text')->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
      ['label' => 'Description', 'href' => '#description'],
      ['label' => 'Files', 'href' => '#files'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    $this->assertContentHeader($content_items[0], 'Details', 'details');
    $this->assertContentHeader($content_items[1], 'Description', 'description');
    $this->assertContentHeader($content_items[2], 'Files', 'files');

    $body = $content_items[1]->findAll('css', '.ecl-row .ecl-col-12.ecl-col-md-9 .ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals('Publication body text', $body[0]->getText());
    $thumbnail_wrapper_selector = '.ecl-row .ecl-col-12.ecl-col-md-3 figure';
    $this->assertSession()->elementNotExists('css', $thumbnail_wrapper_selector);

    // Assert Thumbnail field.
    $media_image = $this->createMediaImage('publication_image');
    $node->set('oe_publication_thumbnail', $media_image)->save();
    $this->drupalGet($node->toUrl());

    $thumbnail_wrapper = $this->assertSession()->elementExists('css', $thumbnail_wrapper_selector);
    $image_element = $this->assertSession()->elementExists('css', 'img', $thumbnail_wrapper);
    $this->assertContains("placeholder_publication_image.png", $image_element->getAttribute('src'));
    $this->assertContains("oe_theme_publication_thumbnail", $image_element->getAttribute('src'));
    $this->assertEquals("Alternative text publication_image", $image_element->getAttribute('alt'));

    // Assert Contact field.
    $contact = $this->createContactEntity('publication_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);
    $node->set('oe_publication_contacts', $contact)->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = ['label' => 'Contact', 'href' => '#contact'];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(4, $content_items);
    $this->assertContentHeader($content_items[3], 'Contact', 'contact');
    $this->assertContactEntityDefaultDisplay($content_items[3], 'publication_contact');
  }

}
