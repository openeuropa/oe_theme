<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_organisation\Entity\OrganisationInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our Project content type renders correctly.
 *
 * @group batch3
 */
class ContentProjectRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_entity_contact',
    'oe_theme_content_project',
    'block',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->markTestSkipped('Skip this test temporarily, as part of ECL v3 upgrade.');

    // Give anonymous users permission to view entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published oe_organisation')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the Project page renders correctly.
   */
  public function testProjectRendering(): void {
    // Create a document for Project results.
    $media_project_result = $this->createMediaDocument('project_result');

    // Create a document for Documents.
    $media_project_document = $this->createMediaDocument('project_document');

    // Create featured media.
    $media_featured = $this->createMediaImage('project_featured_media');

    // Create organisations for Coordinators and Participants fields.
    // Unpublished entity should not be shown.
    $coordinator_organisation = $this->createStakeholderOrganisationEntity('coordinator', CorporateEntityInterface::PUBLISHED);
    $participant_organisation = $this->createStakeholderOrganisationEntity('participant', CorporateEntityInterface::NOT_PUBLISHED);

    // Create a Project node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_project',
      'title' => 'Test project node',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
      'oe_featured_media' => [
        'target_id' => (int) $media_featured->id(),
        'caption' => "Caption project_featured_media",
      ],
      'oe_project_calls' => [
        [
          'uri' => 'http://proposal-call.com',
          'title' => 'Test call for proposal',
        ],
        [
          'uri' => 'http://proposal-call-no-title.com',
        ],
      ],
      'oe_project_results' => 'Project results...',
      'oe_project_result_files' => [
        [
          'target_id' => (int) $media_project_result->id(),
        ],
      ],
      'oe_project_dates' => [
        'value' => '2020-05-10',
        'end_value' => '2025-05-15',
      ],
      'oe_project_budget' => '100',
      'oe_project_budget_eu' => '100',
      'oe_project_website' => [
        [
          'uri' => 'http://example.com',
          'title' => 'Example website',
        ],
      ],
      'oe_reference_code' => 'Project reference',
      'oe_project_locations' => [
        [
          'country_code' => 'ES',
          'administrative_area' => 'Burgos',
          'locality' => 'Ages',
          'postal_code' => '09199',
        ],
        [
          'country_code' => 'DE',
          'locality' => 'Munich',
        ],
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_project_funding_programme' => 'http://publications.europa.eu/resource/authority/eu-programme/AFIS2020',
      'oe_project_coordinators' => [$coordinator_organisation],
      'oe_project_participants' => [$participant_organisation],
      'oe_documents' => [
        [
          'target_id' => (int) $media_project_document->id(),
        ],
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $expected_values = [
      'title' => 'Test project node',
      'description' => 'Summary',
      'meta' => ['Project'],
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Assert top region - Project details.
    $project_details = $this->assertSession()->elementExists('css', 'div#project-details');

    // Assert the body text.
    $this->assertContains('Body', $project_details->getText());
    $this->assertFeaturedMediaField($project_details, 'project_featured_media');

    // Assert the description blocks inside the Project details.
    $description_lists = $project_details->findAll('css', '.ecl-col-12.ecl-col-m-6.ecl-u-mt-l.ecl-u-mt-md-none .ecl-u-mb-s');
    $this->assertCount(3, $description_lists);

    // Assert the first description list block's labels and values.
    $field_list_assert = new FieldListAssert();
    $first_field_list_expected_values = [
      'items' => [
        [
          'label' => 'Reference',
          'body' => 'Project reference',
        ], [
          'label' => 'Project duration',
          'body' => "10.05.2020\n - 15.05.2025",
        ], [
          'label' => 'Project locations',
          'body' => "09199 Ages Burgos, Spain\n\n  Munich, Germany",
        ],
      ],
    ];
    $field_list_html = $description_lists[0]->getHtml();
    $field_list_assert->assertPattern($first_field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('featured_horizontal', $field_list_html);

    // Assert the second description list block's labels and values.
    $second_field_list_expected_values = [
      'items' => [
        [
          'label' => 'Overall budget',
          'body' => '€100',
        ], [
          'label' => 'EU contribution',
          'body' => "€100100% of the overall budget",
        ],
      ],
    ];
    $field_list_html = $description_lists[1]->getHtml();
    $field_list_assert->assertPattern($second_field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('featured_horizontal', $field_list_html);

    // Change EU contribution and assert percentage field change.
    $node->set('oe_project_budget_eu', 50);
    // Change Project duration to test label when start date equals end date.
    $node->set('oe_project_dates', [
      'value' => '2020-05-10',
      'end_value' => '2020-05-10',
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert the first description list block's labels and values.
    $first_field_list_expected_values['items'][1] = [
      'label' => 'Start date',
      'body' => '10.05.2020',
    ];
    $field_list_assert->assertPattern($first_field_list_expected_values, $description_lists[0]->getHtml());

    // Assert the second description list block's labels and values.
    $second_field_list_expected_values['items'][1]['body'] = "€5050% of the overall budget";
    $field_list_assert->assertPattern($second_field_list_expected_values, $description_lists[1]->getHtml());

    // Assert the third description list block's labels and values.
    $third_field_list_expected_values = [
      'items' => [
        [
          'label' => 'Project website',
          'body' => 'Example website',
        ],
      ],
    ];
    $field_list_html = $description_lists[2]->getHtml();
    $field_list_assert->assertPattern($third_field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('featured_horizontal', $field_list_html);

    // Assert documents file.
    $file_wrapper = $this->assertSession()->elementExists('css', 'div#project-documents');
    $this->assertMediaDocumentDefaultRender($file_wrapper, 'project_document', 'English', '2.96 KB - PDF', "sample_project_document.pdf", 'Download');

    // Assert top region - Project results.
    $project_results = $this->assertSession()->elementExists('css', 'div#project-results');

    // Assert results text.
    $this->assertContains('Project results...', $project_results->getText());

    // Assert result file.
    $file_wrapper = $project_results->find('css', 'div.ecl-file');
    $this->assertMediaDocumentDefaultRender($file_wrapper, 'project_result', 'English', '2.96 KB - PDF', "sample_project_result.pdf", 'Download');

    // Assert funding programme.
    $project_funding = $this->assertSession()->elementExists('css', 'div#project-funding');
    $this->assertContentHeader($project_funding, 'Funding');
    $unordered_list_items = $project_funding->findAll('css', 'ul.ecl-unordered-list.ecl-unordered-list--divider');
    $this->assertCount(2, $unordered_list_items);

    $funding_items = $unordered_list_items[0]->findAll('css', '.ecl-unordered-list__item');
    $this->assertCount(1, $funding_items);
    $this->assertListItem($funding_items[0], 'Anti Fraud Information System (AFIS)', 'Funding programme');

    $proposal_items = $unordered_list_items[1]->findAll('css', '.ecl-unordered-list__item');
    $this->assertCount(2, $proposal_items);
    $this->assertListItem($proposal_items[0], 'Test call for proposal', 'Call for proposals', 'http://proposal-call.com');
    $this->assertListItem($proposal_items[1], 'http://proposal-call-no-title.com', 'Call for proposals', 'http://proposal-call-no-title.com');

    // Assert bottom region - Stakeholders.
    $project_stakeholders = $this->assertSession()->elementExists('css', 'div#project-stakeholders');
    $this->assertContentHeader($project_stakeholders, 'Stakeholders');

    // Assert Coordinators field.
    $stakeholder_sub_headers = $project_stakeholders->findAll('css', 'h3.ecl-u-type-heading-3.ecl-u-type-color-black.ecl-u-mt-none.ecl-u-mb-m.ecl-u-mb-md-l');
    $this->assertCount(1, $stakeholder_sub_headers);
    $this->assertEquals($stakeholder_sub_headers[0]->getText(), 'Coordinators');
    $this->assertStakeholderOrganisationRendering($project_stakeholders, 'coordinator');

    // Load logo that is unpublished and assert that is not rendered.
    $media = $this->getStorage('media')->loadByProperties(['name' => 'Test image coordinator']);
    $media = reset($media);
    $media->set('status', 0)->save();

    $this->drupalGet($node->toUrl());
    $this->assertEmpty($project_stakeholders->findAll('css', 'div[role=img]'));

    // Unpublish Coordinator and publish Participant organisations.
    $coordinator_organisation->set('status', CorporateEntityInterface::NOT_PUBLISHED);
    $coordinator_organisation->save();
    $participant_organisation->set('status', CorporateEntityInterface::PUBLISHED);
    $participant_organisation->save();
    $this->drupalGet($node->toUrl());

    // Assert Participants field.
    $stakeholder_sub_headers = $project_stakeholders->findAll('css', 'h3.ecl-u-type-heading-3.ecl-u-type-color-black.ecl-u-mt-none.ecl-u-mb-m.ecl-u-mb-md-l');
    $this->assertCount(1, $stakeholder_sub_headers);
    $this->assertEquals($stakeholder_sub_headers[0]->getText(), 'Participants');
    $this->assertStakeholderOrganisationRendering($project_stakeholders, 'participant');

    // Assert Project's contacts.
    $contact = $this->createContactEntity('project_contact');
    $node->set('oe_project_contact', [$contact])->save();
    $this->drupalGet($node->toUrl());

    $project_contacts = $this->assertSession()->elementExists('css', 'div#project-contacts');
    $this->assertContentHeader($project_contacts, 'Contact');
    $this->assertContactDefaultRender($project_contacts, 'project_contact');

    // Unpublish Contact entity to test its visibility.
    $contact = $node->get('oe_project_contact')->entity;
    $contact->set('status', CorporateEntityInterface::NOT_PUBLISHED);
    $contact->save();
    $this->drupalGet($node->toUrl());

    $this->assertSession()->elementNotExists('css', 'div#project-contacts');
  }

  /**
   * Creates a stakeholder organisation entity.
   *
   * @param string $name
   *   Name of the entity. Is used as a parameter for test data.
   * @param int $status
   *   Entity status. 1 - published, 0 - unpublished.
   *
   * @return \Drupal\oe_content_entity_organisation\Entity\OrganisationInterface
   *   Organisation entity.
   */
  protected function createStakeholderOrganisationEntity(string $name, int $status): OrganisationInterface {
    // Create image for logo.
    $media = $this->createMediaImage($name);

    $organisation = $this->getStorage('oe_organisation')->create([
      'bundle' => 'oe_stakeholder',
      'name' => $name,
      'oe_acronym' => "Acronym $name",
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => "Address $name",
        'postal_code' => '1001',
      ],
      'oe_contact_url' => ['uri' => "http://example.com/contact_$name"],
      'oe_logo' => [
        [
          'target_id' => (int) $media->id(),
        ],
      ],
      'oe_website' => ['uri' => "http://www.example.com/website_$name"],
      'status' => $status,
    ]);
    $organisation->save();

    return $organisation;
  }

  /**
   * Asserts rendering of a Stakeholder.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_stakeholder_element
   *   Stakeholder group.
   * @param string $name
   *   Name of the entity.
   */
  protected function assertStakeholderOrganisationRendering(NodeElement $rendered_stakeholder_element, string $name): void {
    $headers = $rendered_stakeholder_element->findAll('css', 'article h4');
    $this->assertEquals("$name (Acronym $name)", $headers[0]->getText());

    // Assert logo.
    $logos = $rendered_stakeholder_element->findAll('css', 'div[role=img]');
    $this->assertCount(1, $logos);
    $this->assertContains("placeholder_$name.png", $logos[0]->getAttribute('style'));

    // Assert the Organisation contacts list block's labels and values.
    $field_list_assert = new FieldListAssert();
    $first_field_list_expected_values = [
      'items' => [
        [
          'label' => 'Address',
          'body' => "Address $name, 1001 Brussels, Belgium",
        ], [
          'label' => 'Website',
          'body' => "http://www.example.com/website_$name",
        ],
      ],
    ];
    $field_list_wrapper = $rendered_stakeholder_element->find('css', '.ecl-u-flex-grow-1.ecl-u-type-color-grey');
    $field_list_html = $field_list_wrapper->getHtml();
    $field_list_assert->assertPattern($first_field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('horizontal', $field_list_html);

    // Assert contact link.
    $contact_links = $rendered_stakeholder_element->findAll('css', '.ecl-link');
    $this->assertCount(1, $contact_links);
    $this->assertContains("http://example.com/contact_$name", $contact_links[0]->getAttribute('href'));
    $contact_link_labels = $rendered_stakeholder_element->findAll('css', '.ecl-link__label');
    $this->assertCount(1, $contact_link_labels);
    $this->assertEquals('Contact organisation', $contact_link_labels[0]->getText());
  }

  /**
   * Asserts list items.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_element
   *   Rendered element.
   * @param string $title
   *   Title of the list item.
   * @param string $meta
   *   Meta value of the list item.
   * @param string $link
   *   Link that is used.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function assertListItem(NodeElement $rendered_element, string $title, string $meta, $link = ''): void {
    $list_item_assert = new ListItemAssert();
    $expected_values = [
      'meta' => $meta,
      'title' => $title,
    ];
    $html = $rendered_element->getHtml();
    $list_item_assert->assertPattern($expected_values, $html);
    $list_item_assert->assertVariant('default', $html);

    // Assert css class for meta.
    $field_meta = $this->assertSession()->elementExists('css', 'span.ecl-u-type-uppercase', $rendered_element);
    $this->assertEquals($meta, $field_meta->getText());

    if (!empty($link)) {
      $link_tag = $rendered_element->find('css', '.ecl-link');
      $this->assertEquals($link, $link_tag->getAttribute('href'));
    }
  }

}
