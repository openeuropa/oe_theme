<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
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

    // Give anonymous users permission to view entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->save();
  }

  /**
   * Tests that the Call for tenders page renders correctly.
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
      'oe_documents' => [
        [
          'target_id' => (int) $media_document->id(),
        ],
      ],
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
    $assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Files', 'href' => '#files'],
      ],
    ];
    $assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

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
    $this->assertContentHeader($content_items[0], 'Files', 'files');
    $this->assertMediaDocumentDefaultRender($content_items[0], 'publication_document');

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

    // Assert Identifier code field.
    $node->set('oe_reference_code', 'Publication identifier code')->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values = [
      'items' => [
        [
          'label' => 'Identification',
          'body' => 'Publication identifier code',
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
          'body' => 'Publication identifier code',
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
    $node->set('oe_publication_country', 'http://publications.europa.eu/resource/authority/country/GBR');
    $node->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][] = [
      'label' => 'Country',
      'body' => 'United Kingdom',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    // Assert multiple Country field.
    $node->set('oe_publication_country', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/country/GBR'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/country/FRA'],
    ])->save();

    $details_expected_values['items'][3] = [
      'label' => 'Countries',
      'body' => 'United Kingdom, France',
    ];
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());
  }

  /**
   * Asserts field group header.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Field group content.
   * @param string $title
   *   Expected title.
   * @param string $id
   *   Expected id.
   */
  protected function assertContentHeader(NodeElement $element, string $title, string $id = ''): void {
    $header = $element->find('css', 'h2.ecl-u-type-heading-2');
    $this->assertEquals($title, $header->getText());
    if (!empty($id)) {
      $this->assertEquals($id, $header->getAttribute('id'));
    }
  }

}
