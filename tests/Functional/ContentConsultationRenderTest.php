<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Component\Utility\Html;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Test Consultation content type rendering.
 */
class ContentConsultationRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'block',
    'system',
    'path',
    'field_group',
    'oe_theme_helper',
    'oe_theme_content_consultation',
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
   * Tests Consultation full view mode rendering.
   */
  public function testTenderRendering(): void {
    // Create document and image media.
    $document = $this->createMediaDocument('consultation_document');
    // Create general contact.
    $contact = $this->createContactEntity('consultation_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);

    // Create a Consultation node.
    $next_year = date('Y') + 1;
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_consultation',
      'title' => 'Test Consultation node',
      'oe_consultation_additional_info' => 'Additional information text',
      'oe_consultation_outcome' => 'Consultation outcome text',
      'oe_consultation_outcome_files' => [
        [
          'target_id' => (int) $document->id(),
        ],
      ],
      'oe_consultation_contacts' => $contact,
      'oe_summary' => 'Consultation introduction',
      'oe_consultation_opening_date' => [
        'value' => '2020-04-15',
      ],
      'oe_consultation_deadline' => [
        'value' => $next_year . '-06-10T23:30:00',
      ],
      'oe_consultation_guidelines' => 'Consultation guidelines text',
      'oe_consultation_closed_text' => 'Consultation closed status text',
      'oe_consultation_legal_info' => 'Legal info text',
      'oe_consultation_target_audience' => 'Target audience text',
      'oe_departments' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'oe_consultation_aim' => 'Consultation aim text',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Consultation | Open');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core h1.ecl-page-header-core__title', 'Test Consultation node');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__description', 'Consultation introduction');

    // Assert navigation part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation', $wrapper);
    $navigation_title = $navigation->find('css', '.ecl-inpage-navigation__title');
    $this->assertEquals('Page contents', $navigation_title->getText());
    $navigation_list = $this->assertSession()->elementExists('css', '.ecl-inpage-navigation__list', $wrapper);
    $navigation_list_items = $navigation_list->findAll('css', '.ecl-inpage-navigation__item');
    $this->assertCount(8, $navigation_list_items);
    $navigation_list_items_labels = [
      'Details',
      'Target audience',
      'Why we are consulting',
      'Responding to the consultation',
      'Consultation outcome',
      'Additional information',
      'Legal notice',
      'Contact',
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
    $this->assertCount(8, $content_items);

    // Assert 1st inpage navigation item content.
    $this->assertContentHeader($content_items[0], 'Details', 'details');
    $field_list = $content_items[0]->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal');
    $this->assertCount(1, $field_list);
    $labels = $field_list[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(4, $labels);
    $labels_data = [
      'Status',
      'Opening date',
      'Deadline',
      'Department',
    ];
    foreach ($labels as $index => $element) {
      $this->assertEquals($labels_data[$index], $element->getText());
    }
    $values = $field_list[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(4, $values);
    $values_data = [
      'Open',
      '15 April 2020',
      "11 June $next_year, 09:30 (AEST)",
      'Audit Board of the European Communities',
    ];
    foreach ($values_data as $index => $value) {
      $this->assertEquals($value, $values[$index]->getText());
    }
    // Set multiple values for Departments and assert the label is updated.
    $node->set('oe_departments', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertEquals('Departments', $labels[3]->getText());
    $this->assertEquals('Audit Board of the European Communities | Associated African States and Madagascar', $values[3]->getText());

    // Assert 2nd inpage navigation item content.
    $this->assertContentHeader($content_items[1], 'Target audience', 'target-audience');
    $content_second_group = $content_items[1]->find('css', '.ecl-editor p');
    $this->assertEquals('Target audience text', $content_second_group->getText());

    // Assert 3rd inpage navigation item content.
    $this->assertContentHeader($content_items[2], 'Why we are consulting', 'why-we-are-consulting');
    $content_second_group = $content_items[2]->find('css', '.ecl-editor p');
    $this->assertEquals('Consultation aim text', $content_second_group->getText());

    // Assert 4th inpage navigation item content.
    $this->assertContentHeader($content_items[3], 'Responding to the consultation', 'responding-to-the-consultation');
    $content_second_group = $content_items[3]->find('css', '.ecl-editor p');
    $this->assertEquals('Consultation guidelines text', $content_second_group->getText());
    $this->assertElementNotPresent('.ecl-link.ecl-link--cta');

    // Add a link to respond button and assert default label.
    $node->set('oe_consultation_response_button', [
      'uri' => 'http://example.com',
    ])->save();
    $this->drupalGet($node->toUrl());
    $respond_button = $content_items[3]->find('css', '.ecl-link.ecl-link--cta');
    $this->assertEquals('Respond to the questionnaire', $respond_button->getText());
    // Add a link to respond button and assert default label.
    $node->set('oe_consultation_response_button', [
      'uri' => 'http://example.com',
      'title' => 'Link text',
    ])->save();
    $this->drupalGet($node->toUrl());
    $respond_button = $content_items[3]->find('css', '.ecl-link.ecl-link--cta');
    $this->assertEquals('Link text', $respond_button->getText());

    // Assert 5th inpage navigation item content.
    $this->assertContentHeader($content_items[4], 'Consultation outcome', 'consultation-outcome');
    $content_second_group = $content_items[4]->find('css', '.ecl-editor p');
    $this->assertEquals('Consultation outcome text', $content_second_group->getText());
    $this->assertMediaDocumentDefaultRender($content_items['4'], 'consultation_document');

    // Assert 6th inpage navigation item content.
    $this->assertContentHeader($content_items[5], 'Additional information', 'additional-information');
    $content_second_group = $content_items[5]->find('css', '.ecl-editor p');
    $this->assertEquals('Additional information text', $content_second_group->getText());

    // Assert 7th inpage navigation item content.
    $this->assertContentHeader($content_items[6], 'Legal notice', 'legal-notice');
    $content_second_group = $content_items[6]->find('css', '.ecl-editor p');
    $this->assertEquals('Legal info text', $content_second_group->getText());

    // Assert 8th inpage navigation item content.
    $this->assertContactEntityDefaultDisplay($content_items[7], 'consultation_contact');

    // Update date values and assert status and respond to the consultation is
    // updated.
    $node->set('oe_consultation_opening_date', ['value' => $next_year . '-05-31']);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertStatusValue($content, 'Upcoming');
    $this->assertOpeningDateValue($content, "31 May $next_year");
    $this->assertDeadlineDateValue($content, "11 June $next_year, 09:30 (AEST)");
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Consultation | Upcoming');

    // Assert status "Closed".
    $node->set('oe_consultation_opening_date', ['value' => '2020-05-31']);
    $node->set('oe_consultation_deadline', ['value' => '2020-05-31T23:30:00']);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertStatusValue($content, 'Closed');
    $this->assertOpeningDateValue($content, '31 May 2020');
    $this->assertDeadlineDateValue($content, '01 June 2020, 09:30 (AEST)');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Consultation | Closed');
    // Assert 4th inpage navigation item content updated.
    $this->assertContentHeader($content_items[3], 'Responding to the consultation', 'responding-to-the-consultation');
    $content_second_group = $content_items[3]->find('css', '.ecl-editor p');
    $this->assertEquals('Consultation closed status text', $content_second_group->getText());
    $this->assertElementNotPresent('.ecl-link.ecl-link--cta');
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
    $selector = '//*[text() = "Status"]/following-sibling::dd[1]/div/span[@class="consultation-status ecl-u-text-uppercase"]';
    $this->assertSession()->elementExists('xpath', $selector);
    $this->assertEquals($expected, $element->find('xpath', $selector)->getText());
  }

  /**
   * Asserts deadline data field value.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $expected
   *   Expected value.
   */
  protected function assertDeadlineDateValue(NodeElement $element, string $expected): void {
    $this->assertEquals($expected, $element->find('xpath', '//*[text() = "Deadline"]/following-sibling::dd/div/time')->getText());
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
