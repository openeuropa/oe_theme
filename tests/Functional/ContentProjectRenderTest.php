<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_organisation\Entity\OrganisationInterface;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our Project content type renders correctly.
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

    // Give anonymous users permission to view organisation entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
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
      'meta' => 'Project',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Assert top region - Project details.
    $project_details = $this->assertSession()->elementExists('css', 'div#project-details');

    // Assert the body text.
    $this->assertContains('Body', $project_details->getText());
    $this->assertFeaturedMediaField($project_details, 'project_featured_media');

    // Assert the description blocks inside the Project details.
    $description_lists = $project_details->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal.ecl-description-list--featured');
    $this->assertCount(3, $description_lists);

    // Assert the first description list block's labels and values.
    $labels = $description_lists[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(3, $labels);
    $this->assertEquals('Reference', $labels[0]->getText());
    $this->assertEquals('Project duration', $labels[1]->getText());
    $this->assertEquals('Project locations', $labels[2]->getText());
    $values = $description_lists[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(3, $values);
    $this->assertEquals('Project reference', $values[0]->getText());
    $this->assertEquals('10.05.2020 - 15.05.2025', $values[1]->getText());
    $this->assertContains('09199 Ages Burgos, Spain', $values[2]->getText());
    $this->assertContains('Munich, Germany', $values[2]->getText());

    // Assert the second description list block's labels and values.
    $labels = $description_lists[1]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(2, $labels);
    $this->assertEquals('Overall budget', $labels[0]->getText());
    $this->assertEquals('EU contribution', $labels[1]->getText());

    // Assert definition content.
    $values = $description_lists[1]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertEquals('<div content="100">€100</div>', trim($values[0]->getHtml()));
    $definition_element = $values[1];
    $values = $definition_element->findAll('css', 'div');
    $this->assertEquals('<div>€100</div>', trim($values[0]->getOuterHtml()));
    $this->assertEquals('<div class="ecl-u-mt-m">100% of the overall budget</div>', trim($values[1]->getOuterHtml()));

    // Change EU contribution and assert percentage field change.
    $node->set('oe_project_budget_eu', 50);
    // Change Project duration to test label when start date equals end date.
    $node->set('oe_project_dates', [
      'value' => '2020-05-10',
      'end_value' => '2020-05-10',
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $description_lists = $project_details->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal.ecl-description-list--featured');
    // Assert the first description list block's labels and values.
    $labels = $description_lists[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertEquals('Start date', $labels[1]->getText());
    $values = $description_lists[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertEquals('10.05.2020', $values[1]->getText());

    // Assert the second description list block's labels and values.
    $values = $description_lists[1]->findAll('css', 'dd.ecl-description-list__definition');
    $definition_element = $values[1];
    $values = $definition_element->findAll('css', 'div');
    $this->assertEquals('<div>€50</div>', trim($values[0]->getOuterHtml()));
    $this->assertEquals('<div class="ecl-u-mt-m">50% of the overall budget</div>', trim($values[1]->getOuterHtml()));

    // Assert the third description list block's labels and values.
    $labels = $description_lists[2]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(1, $labels);
    $this->assertEquals('Project website', $labels[0]->getText());
    $values = $description_lists[2]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(1, $values);
    $values[0]->hasLink('Example website');

    // Assert documents file.
    $file_wrapper = $this->assertSession()->elementExists('css', 'div#project-documents');
    $file_row = $file_wrapper->find('css', '.ecl-file .ecl-file__container');
    $file_title = $file_row->find('css', '.ecl-file__title');
    $this->assertContains('Test document project_document', $file_title->getText());
    $file_info_language = $file_row->find('css', '.ecl-file__info div.ecl-file__language');
    $this->assertContains('English', $file_info_language->getText());
    $file_info_properties = $file_row->find('css', '.ecl-file__info div.ecl-file__meta');
    $this->assertContains('(2.96 KB - PDF)', $file_info_properties->getText());
    $file_download_link = $file_row->find('css', '.ecl-file__download');
    $this->assertContains('/sample_project_document.pdf', $file_download_link->getAttribute('href'));
    $this->assertContains('Download', $file_download_link->getText());

    // Assert top region - Project results.
    $project_results = $this->assertSession()->elementExists('css', 'div#project-results');

    // Assert results text.
    $this->assertContains('Project results...', $project_results->getText());

    // Assert result file.
    $file_wrapper = $project_results->find('css', 'div.ecl-file');
    $file_row = $file_wrapper->find('css', '.ecl-file .ecl-file__container');
    $file_title = $file_row->find('css', '.ecl-file__title');
    $this->assertContains('Test document project_result', $file_title->getText());
    $file_info_language = $file_row->find('css', '.ecl-file__info div.ecl-file__language');
    $this->assertContains('English', $file_info_language->getText());
    $file_info_properties = $file_row->find('css', '.ecl-file__info div.ecl-file__meta');
    $this->assertContains('KB - PDF)', $file_info_properties->getText());
    $file_download_link = $file_row->find('css', '.ecl-file__download');
    $this->assertContains('/sample_project_result.pdf', $file_download_link->getAttribute('href'));
    $this->assertContains('Download', $file_download_link->getText());

    // Assert funding programme.
    $project_funding = $this->assertSession()->elementExists('css', 'div#project-funding');
    $title = $project_funding->find('css', '.ecl-u-type-heading-2');
    $this->assertContains('Funding', $title->getText());
    $item = $project_funding->findAll('css', '.ecl-unordered-list__item');
    $this->assertCount(3, $item);
    $meta = $item[0]->find('css', '.ecl-content-item__meta span.ecl-u-type-uppercase');
    $this->assertEquals('Funding programme', $meta->getText());
    $title = $item[0]->find('css', '.ecl-content-item__title');
    $this->assertContains('Anti Fraud Information System (AFIS)', $title->getText());
    $meta = $item[1]->find('css', '.ecl-content-item__meta span.ecl-u-type-uppercase');
    $this->assertEquals('Call for proposals', $meta->getText());
    $link = $item[1]->find('css', '.ecl-link');
    $this->assertContains('Test call for proposal', $link->getText());
    $this->assertContains('http://proposal-call.com', $link->getAttribute('href'));
    $meta = $item[2]->find('css', '.ecl-content-item__meta span.ecl-u-type-uppercase');
    $this->assertEquals('Call for proposals', $meta->getText());
    $link = $item[2]->find('css', '.ecl-link');
    $this->assertContains('http://proposal-call-no-title.com', $link->getText());
    $this->assertContains('http://proposal-call-no-title.com', $link->getAttribute('href'));

    // Assert bottom region - Stakeholders.
    $project_stakeholders = $this->assertSession()->elementExists('css', 'div#project-stakeholders');

    // Assert header.
    $stakeholder_headers = $project_stakeholders->findAll('css', 'h2');
    $this->assertEquals($stakeholder_headers[0]->getText(), 'Stakeholders');

    // Assert Coordinators field.
    $stakeholder_sub_headers = $project_stakeholders->findAll('css', 'h3');
    $this->assertCount(1, $stakeholder_sub_headers);
    $this->assertEquals($stakeholder_sub_headers[0]->getText(), 'Coordinators');
    $this->assertStakeholderOrganisationRendering($project_stakeholders, 'coordinator');

    // Unpublish Coordinator and publish Participant organisations.
    $coordinator_organisation->set('status', CorporateEntityInterface::NOT_PUBLISHED);
    $coordinator_organisation->save();
    $participant_organisation->set('status', CorporateEntityInterface::PUBLISHED);
    $participant_organisation->save();

    // Reload the page.
    $this->drupalGet($node->toUrl());

    // Assert Participants field.
    $project_stakeholders = $this->assertSession()->elementExists('css', 'div#project-stakeholders');
    $stakeholder_sub_headers = $project_stakeholders->findAll('css', 'h3');
    $this->assertCount(1, $stakeholder_sub_headers);
    $this->assertEquals($stakeholder_sub_headers[0]->getText(), 'Participants');
    $this->assertStakeholderOrganisationRendering($project_stakeholders, 'participant');

    // Assert Project's contacts.
    $this->assertContactEntityDefaultDisplay($node, 'oe_project_contact');
    $project_contacts = $this->assertSession()->elementExists('css', 'div#project-contacts');
    $this->assertContentHeader($project_contacts, 'Contact');

    // Unpublish Contact entity to test its visibility.
    $general_contact = $node->get('oe_project_contact')->entity;
    $general_contact->set('status', CorporateEntityInterface::NOT_PUBLISHED);
    $general_contact->save();

    // Reload the page.
    $this->drupalGet($node->toUrl());

    // Asset Contact entity visibility.
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
    $description_lists = $rendered_stakeholder_element->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal');
    $this->assertCount(1, $description_lists);
    $labels = $description_lists[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(2, $labels);
    $this->assertEquals('Address', $labels[0]->getText());
    $this->assertEquals('Website', $labels[1]->getText());
    $values = $description_lists[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(2, $values);
    $this->assertEquals("Address $name, 1001 Brussels, Belgium", $values[0]->getText());
    $values[1]->hasLink("http://www.example.com/website_$name");

    // Assert contact link.
    $contact_links = $rendered_stakeholder_element->findAll('css', '.ecl-link');
    $this->assertCount(1, $contact_links);
    $this->assertContains("http://example.com/contact_$name", $contact_links[0]->getAttribute('href'));
    $contact_link_labels = $rendered_stakeholder_element->findAll('css', '.ecl-link__label');
    $this->assertCount(1, $contact_link_labels);
    $this->assertEquals('Contact organisation', $contact_link_labels[0]->getText());
  }

}
