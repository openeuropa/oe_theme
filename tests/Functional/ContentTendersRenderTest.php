<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Component\Utility\Html;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that Call for tender content type renders correctly.
 */
class ContentTendersRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'block',
    'system',
    'path',
    'oe_theme_helper',
    'oe_theme_content_tender',
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
   * Tests that the Tender page renders correctly.
   */
  public function testTenderRendering(): void {
    // Create a document for Tender documents.
    $media_document = $this->createMediaDocument('call_for_tenders_document');

    // Create a Call for tender node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_tender',
      'title' => 'Test Call for tenders node',
      'body' => 'Call for tenders body',
      'oe_documents' => [
        [
          'target_id' => (int) $media_document->id(),
        ],
      ],
      'oe_summary' => 'Call for tenders introduction',
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
      'oe_teaser' => '',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Call for tenders | Open');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core h1.ecl-page-header-core__title', 'Test Call for tenders node');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__description', 'Call for tenders introduction');

    // Assert navigation part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation', $wrapper);
    $navigation_title = $navigation->find('css', '.ecl-inpage-navigation__title');
    $this->assertEquals('Page contents', $navigation_title->getText());
    $navigation_list = $this->assertSession()->elementExists('css', '.ecl-inpage-navigation__list', $wrapper);
    $navigation_list_items = $navigation_list->findAll('css', '.ecl-inpage-navigation__item');
    $this->assertCount(3, $navigation_list_items);
    $navigation_list_items_labels = [
      'Details',
      'Description',
      'Documents',
    ];
    foreach ($navigation_list_items as $index => $item) {
      $navigation_list_item_link = $item->find('css', 'a.ecl-inpage-navigation__link');
      $this->assertEquals($navigation_list_items_labels[$index], $navigation_list_item_link->getText());
      $anchor = strtolower(Html::cleanCssIdentifier($navigation_list_items_labels[$index]));
      $this->assertEquals('#' . $anchor, $navigation_list_item_link->getAttribute('href'));
    }

    // Assert content part.
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);

    // Assert header of first field group.
    $this->assertContentHeader($content_items[0], 'Details', 'details');

    // Assert labels and values in first field group.
    $field_list = $content_items[0]->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal');
    $this->assertCount(1, $field_list);
    $labels = $field_list[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(6, $labels);
    $labels_data = [
      'Status',
      'Reference',
      'Publication date',
      'Opening date',
      'Deadline date',
      'Department',
    ];
    foreach ($labels as $index => $element) {
      $this->assertEquals($labels_data[$index], $element->getText());
    }
    $values = $field_list[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(6, $values);
    $values_data = [
      'Open',
      'Call for tenders reference',
      '15 April 2020',
      '30 April 2020',
      '11 June 2021, 09:30 (AEST)',
      'Audit Board of the European Communities',
    ];
    foreach ($values_data as $index => $value) {
      $this->assertEquals($value, $values[$index]->getText());
    }

    // Assert header of second field group.
    $this->assertContentHeader($content_items[1], 'Description', 'description');

    // Assert content of second field group.
    $content_second_group = $content_items[1]->find('css', '.ecl-editor p');
    $this->assertEquals('Call for tenders body', $content_second_group->getText());

    // Assert header of third field group.
    $this->assertContentHeader($content_items[2], 'Documents', 'documents');
    $this->assertMediaDocumentDefaultRender($content_items['2'], 'call_for_tenders_document');

    // Assert Responsible department field label.
    $node->set('oe_departments', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertEquals('Departments', $labels[5]->getText());
    $this->assertEquals('Audit Board of the European Communities | Associated African States and Madagascar', $values[5]->getText());

    // Assert status "Upcoming".
    $node->set('oe_tender_opening_date', ['value' => date('Y') + 1 . '-05-31']);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertStatusValue($content, 'Upcoming');
    $this->assertOpeningDateValue($content, '31 May 2021');
    $this->assertDeadlineDateValue($content, '11 June 2021, 09:30 (AEST)');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Call for tenders | Upcoming');

    // Assert status "Closed".
    $node->set('oe_tender_opening_date', ['value' => '2020-05-31']);
    $node->set('oe_tender_deadline', ['value' => '2020-05-31T23:30:00']);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertStatusValue($content, 'Closed');
    $this->assertOpeningDateValue($content, '31 May 2020');
    $this->assertDeadlineDateValue($content, '01 June 2020, 09:30 (AEST)', TRUE);
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Call for tenders | Closed');

    // Assert empty status.
    $node->set('oe_tender_opening_date', ['value' => '']);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertStatusValue($content, 'N/A');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Call for tenders');
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
  protected function assertContentHeader(NodeElement $element, string $title, string $id): void {
    $header = $element->find('css', 'h2.ecl-u-type-heading-2');
    $this->assertEquals($title, $header->getText());
    $this->assertEquals($id, $header->getAttribute('id'));
  }

  /**
   * Asserts status field value.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $expected
   *   Expected value.
   */
  protected function assertStatusValue(NodeElement $element, string $expected): void {
    $this->assertEquals($expected, $element->find('xpath', '//*[text() = "Status"]/following-sibling::dd[1]/span[@class="ecl-u-text-uppercase"]')->getText());
  }

  /**
   * Asserts deadline data field value.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $expected
   *   Expected value.
   * @param bool $is_strike
   *   Whether value should be strike or not.
   */
  protected function assertDeadlineDateValue(NodeElement $element, string $expected, bool $is_strike = FALSE): void {
    $value_wrapper_element = $element->find('xpath', '//*[text() = "Deadline date"]/following-sibling::dd/div');
    if ($is_strike) {
      $this->assertTrue($value_wrapper_element->hasClass('ecl-u-type-strike'));
    }
    else {
      $this->assertFalse($value_wrapper_element->hasClass('ecl-u-type-strike'));
    }
    $this->assertEquals($expected, $element->find('xpath', '//*[text() = "Deadline date"]/following-sibling::dd/div/time')->getText());
  }

  /**
   * Asserts opening date value.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $expected
   *   Expected value.
   */
  protected function assertOpeningDateValue(NodeElement $element, string $expected): void {
    $value_element = $element->find('xpath', '//*[text() = "Opening date"]/following-sibling::dd/div/time');
    $this->assertEquals($expected, $value_element->getText());
  }

}
