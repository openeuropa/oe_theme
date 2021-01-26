<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
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
    'datetime_testing',
    'block',
    'system',
    'path',
    'field_group',
    'oe_content_entity',
    'oe_content_entity_document_reference',
    'oe_theme_helper',
    'oe_theme_content_consultation',
    'oe_theme_content_publication',
    'oe_theme_content_document_reference',
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
      ->grantPermission('view published oe_document_reference')
      ->save();
  }

  /**
   * Tests Consultation full view mode rendering.
   */
  public function testConsultationRendering(): void {
    // Create documents.
    $document = $this->createMediaDocument('consultation_document');
    // Create general contacts.
    $first_contact = $this->createContactEntity('first_consultation_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);
    $second_contact = $this->createContactEntity('second_consultation_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);
    // Create Document reference entities.
    $document_reference = $this->createDocumentReferenceEntity('document_reference', 'oe_document', CorporateEntityInterface::PUBLISHED);
    $publication_reference = $this->createDocumentReferenceEntity('publication_reference', 'oe_publication', CorporateEntityInterface::PUBLISHED);

    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $opening_date = (clone $static_time)->modify('- 5 days');
    $deadline_date = (clone $static_time)->modify('+ 10 days');

    /** @var \Drupal\Component\Datetime\TimeInterface $datetime */
    $time = \Drupal::time();
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());

    // Create a Consultation node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_consultation',
      'title' => 'Test Consultation node',
      'oe_consultation_opening_date' => [
        'value' => $opening_date->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
      ],
      'oe_consultation_deadline' => [
        'value' => $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ],
      'oe_consultation_target_audience' => 'Target audience text',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $expected_values = [
      'title' => 'Test Consultation node',
      'meta' => 'Consultation | Open',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());
    // Add summary and assert header is updated.
    $node->set('oe_summary', 'Consultation introduction');
    $node->save();
    $this->drupalGet($node->toUrl());
    $expected_values = [
      'title' => 'Test Consultation node',
      'description' => 'Consultation introduction',
      'meta' => 'Consultation | Open',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Assert navigation part.
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Assert content part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);
    $this->assertContentHeader($content_items[0], 'Details', 'details');

    // Assert the fields of the details section.
    $field_list_assert = new FieldListAssert();
    $details_expected_values = [];
    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Open',
      ], [
        'label' => 'Opening date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Deadline',
        'body' => '28 February 2020, 01:00 (AEDT)',
      ],
    ];
    $details_html = $content_items[0]->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);
    // Set one department value and assert details section is updated.
    $node->set('oe_departments', [
      'target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Open',
      ], [
        'label' => 'Opening date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Deadline',
        'body' => '28 February 2020, 01:00 (AEDT)',
      ], [
        'label' => 'Department',
        'body' => 'Audit Board of the European Communities',
      ],
    ];
    $details_html = $content_items[0]->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    // Set multiple department values and assert details section is updated.
    $node->set('oe_departments', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Open',
      ], [
        'label' => 'Opening date',
        'body' => '12 February 2020',
      ], [
        'label' => 'Deadline',
        'body' => '28 February 2020, 01:00 (AEDT)',
      ], [
        'label' => 'Departments',
        'body' => 'Audit Board of the European Communities, Associated African States and Madagascar',
      ],
    ];
    $details_html = $content_items[0]->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);

    // Assert 2nd inpage navigation item content.
    $content_second_group = $content_items[1]->find('css', '.ecl-editor p');
    $this->assertEquals('Target audience text', $content_second_group->getText());

    // Set Consultation aim and assert navigation and content is updated.
    $node->set('oe_consultation_aim', 'Consultation aim text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    $content_second_group = $content_items[2]->find('css', '.ecl-editor p');
    $this->assertEquals('Consultation aim text', $content_second_group->getText());

    // Set Consultation guidelines and assert navigation and content is updated.
    $node->set('oe_consultation_guidelines', 'Consultation guidelines text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
        ['label' => 'Respond to the consultation', 'href' => '#respond-to-the-consultation'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(4, $content_items);
    $content_second_group = $content_items[3]->find('css', '.ecl-editor p');
    $this->assertEquals('Consultation guidelines text', $content_second_group->getText());
    $this->assertElementNotPresent('.ecl-link.ecl-link--cta');

    // Add a link to respond button and assert default label.
    $node->set('oe_consultation_response_button', [
      'uri' => 'internal:/node/add',
    ])->save();
    $this->drupalGet($node->toUrl());
    $respond_button = $content_items[3]->find('css', '.ecl-link.ecl-link--cta');
    $this->assertEquals('Respond to the questionnaire', $respond_button->getText());
    // Add a link to respond button and assert default label.
    $node->set('oe_consultation_response_button', [
      'uri' => 'https://example.com',
      'title' => 'Link text',
    ])->save();
    $this->drupalGet($node->toUrl());
    $respond_button = $content_items[3]->find('css', '.ecl-link.ecl-link--cta');
    $this->assertEquals('Link text', $respond_button->getText());

    // Set consultation outcome and outcome files and assert content is updated.
    $node->set('oe_consultation_outcome', 'Consultation outcome text');
    $node->set('oe_consultation_outcome_files', [
      ['target_id' => (int) $document->id()],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
        ['label' => 'Respond to the consultation', 'href' => '#respond-to-the-consultation'],
        ['label' => 'Consultation outcome', 'href' => '#consultation-outcome'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(5, $content_items);
    $content_second_group = $content_items[4]->find('css', '.ecl-editor p');
    $this->assertEquals('Consultation outcome text', $content_second_group->getText());
    $this->assertMediaDocumentDefaultRender($content_items[4], 'consultation_document');

    // Reference documents and publication node and assert content is updated.
    $node->set('oe_consultation_documents', [$document_reference, $publication_reference]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
        ['label' => 'Respond to the consultation', 'href' => '#respond-to-the-consultation'],
        ['label' => 'Consultation outcome', 'href' => '#consultation-outcome'],
        ['label' => 'Reference documents', 'href' => '#reference-documents'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(6, $content_items);
    $this->assertMediaDocumentDefaultRender($content_items[5], 'document_reference');
    $publication_teaser = $content_items[5]->find('css', '.ecl-content-item.ecl-u-d-sm-flex.ecl-u-pb-m');
    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Publication node',
      'meta' => "Abstract | 15 April 2020\n | Associated African States and Madagascar",
      'description' => 'Teaser text',
    ];
    $assert->assertPattern($expected_values, $publication_teaser->getOuterHtml());

    // Set additional information and assert content is updated.
    $node->set('oe_consultation_additional_info', 'Additional information text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
        ['label' => 'Respond to the consultation', 'href' => '#respond-to-the-consultation'],
        ['label' => 'Consultation outcome', 'href' => '#consultation-outcome'],
        ['label' => 'Reference documents', 'href' => '#reference-documents'],
        ['label' => 'Additional information', 'href' => '#additional-information'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(7, $content_items);
    $content_second_group = $content_items[6]->find('css', '.ecl-editor p');
    $this->assertEquals('Additional information text', $content_second_group->getText());

    // Set legal notice and assert content is updated.
    $node->set('oe_consultation_legal_info', 'Legal info text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
        ['label' => 'Respond to the consultation', 'href' => '#respond-to-the-consultation'],
        ['label' => 'Consultation outcome', 'href' => '#consultation-outcome'],
        ['label' => 'Reference documents', 'href' => '#reference-documents'],
        ['label' => 'Additional information', 'href' => '#additional-information'],
        ['label' => 'Legal notice', 'href' => '#legal-notice'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(8, $content_items);
    $content_second_group = $content_items[7]->find('css', '.ecl-editor p');
    $this->assertEquals('Legal info text', $content_second_group->getText());

    // Set contact and assert content is updated.
    $node->set('oe_consultation_contacts', $first_contact);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
        ['label' => 'Respond to the consultation', 'href' => '#respond-to-the-consultation'],
        ['label' => 'Consultation outcome', 'href' => '#consultation-outcome'],
        ['label' => 'Reference documents', 'href' => '#reference-documents'],
        ['label' => 'Additional information', 'href' => '#additional-information'],
        ['label' => 'Legal notice', 'href' => '#legal-notice'],
        ['label' => 'Contact', 'href' => '#contact'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(9, $content_items);
    $this->assertContactEntityDefaultDisplay($content_items[8], 'first_consultation_contact');
    // Set two contacts and assert label is updated.
    $node->set('oe_consultation_contacts', [$first_contact, $second_contact]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Target audience', 'href' => '#target-audience'],
        ['label' => 'Why we are consulting', 'href' => '#why-we-are-consulting'],
        ['label' => 'Respond to the consultation', 'href' => '#respond-to-the-consultation'],
        ['label' => 'Consultation outcome', 'href' => '#consultation-outcome'],
        ['label' => 'Reference documents', 'href' => '#reference-documents'],
        ['label' => 'Additional information', 'href' => '#additional-information'],
        ['label' => 'Legal notice', 'href' => '#legal-notice'],
        ['label' => 'Contacts', 'href' => '#contact'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Update date values and assert status and respond to the consultation is
    // updated.
    $opening_date = (clone $static_time)->modify('+ 3 days');
    $node->set('oe_consultation_opening_date', ['value' => $opening_date->format(DateTimeItemInterface::DATE_STORAGE_FORMAT)]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertStatusValue($content, 'Upcoming');
    $this->assertOpeningDateValue($content, '20 February 2020');
    $this->assertDeadlineDateValue($content, '28 February 2020, 01:00 (AEDT)');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Consultation | Upcoming');

    // Assert status "Closed".
    $opening_date = (clone $static_time)->modify('- 5 days');
    $deadline_date = (clone $static_time)->modify('- 1 days');
    $node->set('oe_consultation_opening_date', ['value' => $opening_date->format(DateTimeItemInterface::DATE_STORAGE_FORMAT)]);
    $node->set('oe_consultation_deadline', ['value' => $deadline_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT)]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertStatusValue($content, 'Closed');
    $this->assertOpeningDateValue($content, '12 February 2020');
    $this->assertDeadlineDateValue($content, '17 February 2020, 01:00 (AEDT)');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__meta', 'Consultation | Closed');
    $this->assertFalse($content_items[3]->hasLink('Link text'));
    // Assert 4th inpage navigation item content is updated.
    $this->assertContentHeader($content_items[3], 'Respond to the consultation', 'respond-to-the-consultation');
    $content_second_group = $content_items[3]->find('css', '.ecl-editor');
    // Assert default value for closed status text.
    $this->assertEquals('The response period for this consultation has ended. Thank you for your input.', $content_second_group->getText());
    $this->assertElementNotPresent('.ecl-link.ecl-link--cta');
    // Set a value and assert the content is updated.
    $node->set('oe_consultation_closed_text', 'Consultation closed status text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertEquals('Consultation closed status text', $content_second_group->getText());
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
