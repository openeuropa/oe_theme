<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\media\MediaInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\oe_content_entity_organisation\Entity\OrganisationInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our Project content type renders correctly.
 */
class ContentProjectRenderTest extends BrowserTestBase {

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
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    \Drupal::service('theme_installer')->install(['oe_theme']);
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'oe_theme')->save();
    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

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

    // Create general contact.
    $general_contact = $this->createContactEntity('general_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);

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
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_project_funding_programme' => 'http://publications.europa.eu/resource/authority/eu-programme/AFIS2020',
      'oe_project_coordinators' => [$coordinator_organisation],
      'oe_project_participants' => [$participant_organisation],
      'oe_project_contact' => [$general_contact],
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
    $this->assertSession()->elementTextContains('css', '.ecl-page-header .ecl-page-header__meta-list', 'Project');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header .ecl-page-header__description', 'Summary');

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
    $file_wrapper = $project_details->find('css', 'div.ecl-file');
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
    $meta = $item[0]->find('css', '.list-item__meta');
    $this->assertEquals('Funding programme', $meta->getText());
    $title = $item[0]->find('css', '.list-item__title');
    $this->assertContains('Anti Fraud Information System (AFIS)', $title->getText());
    $meta = $item[1]->find('css', '.list-item__meta');
    $this->assertEquals('Call for proposals', $meta->getText());
    $link = $item[1]->find('css', '.ecl-link');
    $this->assertContains('Test call for proposal', $link->getText());
    $this->assertContains('http://proposal-call.com', $link->getAttribute('href'));
    $meta = $item[2]->find('css', '.list-item__meta');
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
    $project_contacts = $this->assertSession()->elementExists('css', 'div#project-contacts');
    $contact_headers = $project_contacts->findAll('css', 'h2');
    $this->assertEquals($contact_headers[0]->getText(), 'Contact');
    $this->assertContactRendering($project_contacts, 'general_contact');

    // Unpublish Contact entity to test its visibility.
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
   * Creates Contact entity.
   *
   * @param string $name
   *   Entity name. Is used as a parameter for test data.
   * @param string $bundle
   *   Entity bundle.
   * @param int $status
   *   Entity status.
   *
   * @return \Drupal\oe_content_entity_contact\Entity\ContactInterface
   *   Contact entity.
   */
  protected function createContactEntity(string $name, string $bundle, int $status): ContactInterface {
    // Create image for contact.
    $media = $this->createMediaImage($name);

    $contact = $this->getStorage('oe_contact')->create([
      'bundle' => $bundle,
      'name' => $name,
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => "Address $name",
        'postal_code' => '1001',
      ],
      'oe_body' => "Body text $name",
      'oe_email' => "$name@example.com",
      'oe_fax' => "Fax number $name",
      'oe_mobile' => "Mobile number $name",
      'oe_office' => "Office $name",
      'oe_organisation' => "Organisation $name",
      'oe_phone' => "Phone number $name",
      'oe_press_contact_url' => ['uri' => "http://www.example.com/press_contact_$name"],
      'oe_social_media' => [
        [
          'uri' => "http://www.example.com/social_media_$name",
          'title' => "Social media $name",
          'link_type' => 'facebook',
        ],
      ],
      'oe_website' => ['uri' => "http://www.example.com/website_$name"],
      'oe_image' => [
        [
          'target_id' => (int) $media->id(),
          'caption' => "Caption $name",
        ],
      ],
      'status' => $status,
    ]);

    return $contact;
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
    $this->assertEquals("$name | Acronym $name", $headers[0]->getText());

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

  /**
   * Asserts rendering of Contact entity.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_element
   *   Rendered element.
   * @param string $name
   *   Name of the entity.
   */
  protected function assertContactRendering(NodeElement $rendered_element, string $name): void {
    $contact_sub_headers = $rendered_element->findAll('css', 'h3');
    $this->assertCount(1, $contact_sub_headers);
    $this->assertEquals($contact_sub_headers[0]->getText(), 'general_contact');

    // Body field.
    $body = $rendered_element->findAll('css', '.ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals("Body text $name", $body[0]->getText());

    // Assert list of fields in field_list pattern.
    $description_lists = $rendered_element->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal');
    $this->assertCount(1, $description_lists);

    // Assert labels in list of fields.
    $description_list_labels = $description_lists[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(9, $description_list_labels);
    $labels = [
      'Organisation',
      'Website',
      'Email',
      'Phone number',
      'Mobile number',
      'Fax number',
      'Postal address',
      'Office',
      'Social media',
    ];
    foreach ($labels as $key => $label) {
      $this->assertEquals($label, $description_list_labels[$key]->getText());
    }

    // Assert values in list of fields.
    $values = $description_lists[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(9, $values);
    $this->assertEquals("Organisation $name", $values[0]->getText());

    $values[1]->hasLink("http://www.example.com/website_$name");
    $values[2]->hasLink("$name@example.com");
    $this->assertEquals("Phone number $name", $values[3]->getText());
    $this->assertEquals("Mobile number $name", $values[4]->getText());
    $this->assertEquals("Fax number $name", $values[5]->getText());
    $this->assertEquals("Address $name, 1001 Brussels, Belgium", $values[6]->getText());
    $this->assertEquals("Office $name", $values[7]->getText());

    // Assert social media link.
    $social_media_links = $values[8]->findAll('css', '.ecl-link');
    $this->assertCount(1, $social_media_links);
    $social_media_link_label = $social_media_links[0]->find('css', '.ecl-link__label');
    $this->assertEqual("Social media $name", $social_media_link_label->getText());
    $this->assertContains("http://www.example.com/social_media_$name", $social_media_links[0]->getAttribute('href'));
    $social_media_icon = $social_media_links[0]->find('css', 'use');
    $this->assertContains('facebook', $social_media_icon->getAttribute('xlink:href'));

    // Assert image.
    $this->assertFeaturedMediaField($rendered_element, $name);
  }

  /**
   * Gets the entity type's storage.
   *
   * @param string $entity_type_id
   *   The entity type ID to get a storage for.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The entity type's storage.
   */
  protected function getStorage(string $entity_type_id): EntityStorageInterface {
    return \Drupal::entityTypeManager()->getStorage($entity_type_id);
  }

  /**
   * Creates media image entity.
   *
   * @param string $name
   *   Name of the image media.
   *
   * @return \Drupal\media\MediaInterface
   *   Media image instance.
   */
  protected function createMediaImage(string $name): MediaInterface {
    // Create file instance.
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/placeholder.png'), "public://placeholder_$name.png");
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'image',
      'name' => "Test image $name",
      'oe_media_image' => [
        'target_id' => (int) $file->id(),
        'alt' => "Alternative text $name",
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Creates media document entity.
   *
   * @param string $name
   *   Name of the document media.
   *
   * @return \Drupal\media\MediaInterface
   *   Media document instance.
   */
  protected function createMediaDocument(string $name): MediaInterface {
    // Create file instance.
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), "public://sample_$name.pdf");
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'document',
      'name' => "Test document $name",
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Asserts featured media field rendering.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_element
   *   Rendered element.
   * @param string $name
   *   Name of the image media.
   */
  protected function assertFeaturedMediaField(NodeElement $rendered_element, string $name): void {
    $figures = $rendered_element->findAll('css', 'figure.ecl-media-container');
    $this->assertCount(1, $figures);

    // Assert image tag.
    $image = $figures[0]->find('css', 'img');
    $this->assertContains("placeholder_$name.png", $image->getAttribute('src'));
    $this->assertEquals("Alternative text $name", $image->getAttribute('alt'));

    // Assert caption.
    $caption = $figures[0]->find('css', 'figcaption');
    $this->assertEquals("Caption $name", $caption->getText());
  }

}
