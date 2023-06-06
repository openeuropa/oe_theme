<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\Traits\Core\CronRunTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Test Consultation content type rendering.
 *
 * @group batch1
 */
class ContentConsultationRenderTest extends ContentRenderTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
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
  public function testConsultationRendering(): void {
    // Create documents.
    $document = $this->createMediaDocument('consultation_document');
    // Create general contacts.
    $first_contact = $this->createContactEntity('first_consultation_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);
    $second_contact = $this->createContactEntity('second_consultation_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);
    // Create Document reference entities.
    $document_reference = $this->createDocumentDocumentReferenceEntity('document_reference', SubEntityInterface::PUBLISHED);
    $publication_reference = $this->createPublicationDocumentReferenceEntity('Publication node', SubEntityInterface::PUBLISHED);

    // Freeze the time at a specific point.
    $static_time = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $opening_date = (clone $static_time)->modify('+ 2 days');
    $deadline_date = (clone $static_time)->modify('+ 10 days');

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
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header.ecl-page-header--negative');
    $page_header_assert = new PatternPageHeaderAssert();
    $page_header_expected_values = [
      'title' => 'Test Consultation node',
      'meta' => [
        'Consultation',
        'Upcoming',
      ],
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());
    // Add summary and assert header is updated.
    $node->set('oe_summary', 'Consultation introduction');
    $node->save();
    $this->drupalGet($node->toUrl());
    $page_header_expected_values = [
      'title' => 'Test Consultation node',
      'description' => 'Consultation introduction',
      'meta' => [
        'Consultation',
        'Upcoming',
      ],
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

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
    $content = $this->assertSession()->elementExists('css', '.ecl-col-l-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-l-9', 1);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);
    $this->assertContentHeader($content_items[0], 'Details', 'details');

    // Assert the fields of the details section.
    $field_list_assert = new FieldListAssert();
    $details_expected_values = [];
    $details_expected_values['items'] = [
      [
        'label' => 'Status',
        'body' => 'Upcoming',
      ], [
        'label' => 'Opening date',
        'body' => '19 February 2020',
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
        'body' => 'Upcoming',
      ], [
        'label' => 'Opening date',
        'body' => '19 February 2020',
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
        'body' => 'Upcoming',
      ], [
        'label' => 'Opening date',
        'body' => '19 February 2020',
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
    $content_second_group = $content_items[1]->find('css', '.ecl p');
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
    $content_second_group = $content_items[2]->find('css', '.ecl p');
    $this->assertEquals('Consultation aim text', $content_second_group->getText());

    // Set Consultation guidelines and assert navigation and content is updated.
    $node->set('oe_consultation_guidelines', 'Consultation guidelines text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        [
          'label' => 'Details',
          'href' => '#details',
        ],
        [
          'label' => 'Target audience',
          'href' => '#target-audience',
        ],
        [
          'label' => 'Why we are consulting',
          'href' => '#why-we-are-consulting',
        ],
        [
          'label' => 'Respond to the consultation',
          'href' => '#respond-to-the-consultation',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(4, $content_items);
    $content_second_group = $content_items[3]->find('css', '.ecl p');
    $this->assertEquals('Consultation guidelines text', $content_second_group->getText());
    $this->assertSession()->elementNotExists('css', '.ecl-link.ecl-link--cta');

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
        [
          'label' => 'Details',
          'href' => '#details',
        ],
        [
          'label' => 'Target audience',
          'href' => '#target-audience',
        ],
        [
          'label' => 'Why we are consulting',
          'href' => '#why-we-are-consulting',
        ],
        [
          'label' => 'Respond to the consultation',
          'href' => '#respond-to-the-consultation',
        ],
        [
          'label' => 'Consultation outcome',
          'href' => '#consultation-outcome',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(5, $content_items);
    $content_second_group = $content_items[4]->find('css', '.ecl p');
    $this->assertEquals('Consultation outcome text', $content_second_group->getText());
    $this->assertMediaDocumentDefaultRender($content_items[4], 'consultation_document', 'English', '2.96 KB - PDF', "sample_consultation_document.pdf", 'Download');

    // Reference documents and publication node and assert content is updated.
    $node->set('oe_consultation_documents', [
      $document_reference,
      $publication_reference,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        [
          'label' => 'Details',
          'href' => '#details',
        ],
        [
          'label' => 'Target audience',
          'href' => '#target-audience',
        ],
        [
          'label' => 'Why we are consulting',
          'href' => '#why-we-are-consulting',
        ],
        [
          'label' => 'Respond to the consultation',
          'href' => '#respond-to-the-consultation',
        ],
        [
          'label' => 'Consultation outcome',
          'href' => '#consultation-outcome',
        ],
        [
          'label' => 'Reference documents',
          'href' => '#reference-documents',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(6, $content_items);
    $this->assertMediaDocumentDefaultRender($content_items[5], 'document_reference', 'English', '2.96 KB - PDF', "sample_document_reference.pdf", 'Download');
    $publication_teaser = $content_items[5]->find('css', 'div.ecl-u-border-bottom.ecl-u-border-color-grey-15');
    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Publication node',
      'meta' => [
        'Abstract',
        '15 April 2020',
        'Associated African States and Madagascar',
      ],
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
        [
          'label' => 'Details',
          'href' => '#details',
        ],
        [
          'label' => 'Target audience',
          'href' => '#target-audience',
        ],
        [
          'label' => 'Why we are consulting',
          'href' => '#why-we-are-consulting',
        ],
        [
          'label' => 'Respond to the consultation',
          'href' => '#respond-to-the-consultation',
        ],
        [
          'label' => 'Consultation outcome',
          'href' => '#consultation-outcome',
        ],
        [
          'label' => 'Reference documents',
          'href' => '#reference-documents',
        ],
        [
          'label' => 'Additional information',
          'href' => '#additional-information',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(7, $content_items);
    $content_second_group = $content_items[6]->find('css', '.ecl p');
    $this->assertEquals('Additional information text', $content_second_group->getText());

    // Set legal notice and assert content is updated.
    $node->set('oe_consultation_legal_info', 'Legal info text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        [
          'label' => 'Details',
          'href' => '#details',
        ],
        [
          'label' => 'Target audience',
          'href' => '#target-audience',
        ],
        [
          'label' => 'Why we are consulting',
          'href' => '#why-we-are-consulting',
        ],
        [
          'label' => 'Respond to the consultation',
          'href' => '#respond-to-the-consultation',
        ],
        [
          'label' => 'Consultation outcome',
          'href' => '#consultation-outcome',
        ],
        [
          'label' => 'Reference documents',
          'href' => '#reference-documents',
        ],
        [
          'label' => 'Additional information',
          'href' => '#additional-information',
        ],
        [
          'label' => 'Legal notice',
          'href' => '#legal-notice',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(8, $content_items);
    $content_second_group = $content_items[7]->find('css', '.ecl p');
    $this->assertEquals('Legal info text', $content_second_group->getText());

    // Set contact and assert content is updated.
    $node->set('oe_consultation_contacts', $first_contact);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        [
          'label' => 'Details',
          'href' => '#details',
        ],
        [
          'label' => 'Target audience',
          'href' => '#target-audience',
        ],
        [
          'label' => 'Why we are consulting',
          'href' => '#why-we-are-consulting',
        ],
        [
          'label' => 'Respond to the consultation',
          'href' => '#respond-to-the-consultation',
        ],
        [
          'label' => 'Consultation outcome',
          'href' => '#consultation-outcome',
        ],
        [
          'label' => 'Reference documents',
          'href' => '#reference-documents',
        ],
        [
          'label' => 'Additional information',
          'href' => '#additional-information',
        ],
        [
          'label' => 'Legal notice',
          'href' => '#legal-notice',
        ],
        [
          'label' => 'Contact',
          'href' => '#contact',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(9, $content_items);
    $this->assertContactDefaultRender($content_items[8], 'first_consultation_contact');
    // Set two contacts and assert label is updated.
    $node->set('oe_consultation_contacts', [$first_contact, $second_contact]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        [
          'label' => 'Details',
          'href' => '#details',
        ],
        [
          'label' => 'Target audience',
          'href' => '#target-audience',
        ],
        [
          'label' => 'Why we are consulting',
          'href' => '#why-we-are-consulting',
        ],
        [
          'label' => 'Respond to the consultation',
          'href' => '#respond-to-the-consultation',
        ],
        [
          'label' => 'Consultation outcome',
          'href' => '#consultation-outcome',
        ],
        [
          'label' => 'Reference documents',
          'href' => '#reference-documents',
        ],
        [
          'label' => 'Additional information',
          'href' => '#additional-information',
        ],
        [
          'label' => 'Legal notice',
          'href' => '#legal-notice',
        ],
        [
          'label' => 'Contacts',
          'href' => '#contact',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Assert "Open" status.
    $static_time = new DrupalDateTime('2020-02-20 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items']['0']['body'] = 'Open';
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    $page_header_expected_values['meta'] = [
      'Consultation',
      'Open',
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    $content_second_group = $content_items[3]->find('css', '.ecl');
    $this->assertEquals('Consultation guidelines text', $content_second_group->getText());
    $this->assertStringNotContainsString('The response period for this consultation has ended. Thank you for your input.', $content_second_group->getText());

    // Add a link to respond button and assert default label.
    $node->set('oe_consultation_response_button', [
      'uri' => 'internal:/node/add',
    ])->save();
    $this->drupalGet($node->toUrl());
    $respond_button = $content_items[3]->find('css', '.ecl-link.ecl-link--cta');
    $this->assertEquals('Respond to the questionnaire', $respond_button->getText());
    // Add a link with title to respond button and assert the label is updated
    // and the external icon is rendered.
    $node->set('oe_consultation_response_button', [
      'uri' => 'https://example.com',
      'title' => 'Link text',
    ])->save();
    $this->drupalGet($node->toUrl());
    $respond_button = $content_items[3]->find('css', '.ecl-link.ecl-link--cta');
    $this->assertEquals('Link text', $respond_button->getText());
    $icon = $respond_button->find('css', 'svg.ecl-icon.ecl-icon--2xs.ecl-link__icon');
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $icon->getHtml());

    // Assert status "Closed".
    $static_time = new DrupalDateTime('2020-04-20 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $this->freezeTime($static_time);
    $this->cronRun();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items']['0']['body'] = 'Closed';
    $field_list_assert->assertPattern($details_expected_values, $content_items[0]->getHtml());

    $page_header_expected_values['meta'] = [
      'Consultation',
      'Closed',
    ];
    $page_header_assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());
    $this->assertFalse($content_items[3]->hasLink('Link text'));

    // Assert 4th inpage navigation item content is updated.
    $this->assertContentHeader($content_items[3], 'Respond to the consultation', 'respond-to-the-consultation');
    $content_second_group = $content_items[3]->find('css', '.ecl');
    // Assert default value for closed status text.
    $this->assertEquals('The response period for this consultation has ended. Thank you for your input.', $content_second_group->getText());
    $this->assertSession()->elementNotExists('css', '.ecl-link.ecl-link--cta');

    // Set a value and assert the content is updated.
    $node->set('oe_consultation_closed_text', 'Consultation closed status text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertEquals('Consultation closed status text', $content_second_group->getText());
  }

}
