<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_organisation\Entity\OrganisationInterface;

/**
 * Tests that our Project content type renders correctly.
 */
class ContentProjectRenderTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'composite_reference',
    'config',
    'datetime_range',
    'entity_reference_revisions',
    'path',
    'system',
    'oe_theme_helper',
    'oe_theme_content_project',
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
      ->grantPermission('view published oe_organisation')
      ->save();
  }

  /**
   * Tests that the Project page renders correctly.
   */
  public function testProjectRendering(): void {
    // Create a document for Project results.
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test.pdf');
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'document',
      'name' => 'Test document',
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
      ],
      'uid' => 0,
      'status' => 1,
    ]);

    $media->save();

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
      'oe_project_results' => 'Project results...',
      'oe_project_result_files' => [
        [
          'target_id' => (int) $media->id(),
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
      'oe_reference' => 'Project reference',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_project_coordinators' => [$coordinator_organisation],
      'oe_project_participants' => [$participant_organisation],
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    $this->drupalGet($node->toUrl());

    // Assert top region - Project details.
    $project_details = $this->assertSession()->elementExists('css', 'div#project-details');

    // Assert the body text.
    $this->assertContains('Body', $project_details->getText());

    // Assert the description blocks inside the Project details.
    $description_lists = $project_details->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal.ecl-description-list--featured');
    $this->assertCount(3, $description_lists);

    // Assert the first description list block's labels and values.
    $labels = $description_lists[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(2, $labels);
    $this->assertEquals('Reference', $labels[0]->getText());
    $this->assertEquals('Project duration', $labels[1]->getText());
    $values = $description_lists[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(2, $values);
    $this->assertEquals('Project reference', $values[0]->getText());
    $this->assertEquals('10.05.2020 - 15.05.2025', $values[1]->getText());

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

    // Assert top region - Project results.
    $project_results = $this->assertSession()->elementExists('css', 'div#project-results');

    // Assert results text.
    $this->assertContains('Project results...', $project_results->getText());

    // Assert result file.
    $file_wrapper = $project_results->find('css', 'div.ecl-file');
    $file_row = $file_wrapper->find('css', '.ecl-file .ecl-file__container');
    $file_title = $file_row->find('css', '.ecl-file__title');
    $this->assertContains('Test document', $file_title->getText());
    $file_info_language = $file_row->find('css', '.ecl-file__info div.ecl-file__language');
    $this->assertContains('English', $file_info_language->getText());
    $file_info_properties = $file_row->find('css', '.ecl-file__info div.ecl-file__meta');
    $this->assertContains('KB - PDF)', $file_info_properties->getText());
    $file_download_link = $file_row->find('css', '.ecl-file__download');
    $this->assertContains('/test.pdf', $file_download_link->getAttribute('href'));
    $this->assertContains('Download', $file_download_link->getText());

    // Assert bottom region - Stakeholders.
    $project_stakeholder = $this->assertSession()->elementExists('css', 'div#project-stakeholder');

    // Assert header.
    $stakeholder_headers = $project_stakeholder->findAll('css', 'h2');
    $this->assertEquals($stakeholder_headers[0]->getText(), 'Stakeholders');

    // Assert Coordinators field.
    $stakeholder_sub_headers = $project_stakeholder->findAll('css', 'h3');
    $this->assertCount(1, $stakeholder_sub_headers);
    $this->assertEquals($stakeholder_sub_headers[0]->getText(), 'Coordinators');
    $this->assertStakeholderOrganisationRendering($project_stakeholder, 'coordinator');

    // Unpublish Coordinator and publish Participant organisations.
    $coordinator_organisation->set('status', CorporateEntityInterface::NOT_PUBLISHED);
    $coordinator_organisation->save();
    $participant_organisation->set('status', CorporateEntityInterface::PUBLISHED);
    $participant_organisation->save();

    // Reload the page.
    $this->drupalGet($node->toUrl());

    // Assert Participants field.
    $project_stakeholder = $this->assertSession()->elementExists('css', 'div#project-stakeholder');
    $stakeholder_sub_headers = $project_stakeholder->findAll('css', 'h3');
    $this->assertCount(1, $stakeholder_sub_headers);
    $this->assertEquals($stakeholder_sub_headers[0]->getText(), 'Participants');
    $this->assertStakeholderOrganisationRendering($project_stakeholder, 'participant');
  }

  /**
   * Creates a stakeholder organisation entity.
   *
   * @var string $name
   *   Name of the entity. Is used as a parameter for test data.
   * @var int $status
   *   Entity status. 1 - published, 0 - unpublished.
   *
   * @return \Drupal\oe_content_entity_organisation\Entity\OrganisationInterface
   *   Organisation entity.
   */
  protected function createStakeholderOrganisationEntity($name, $status): OrganisationInterface {
    // Create image for logo.
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/placeholder.png'), "public://placeholder_$name.png");
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'image',
      'name' => "Test image $name",
      'oe_media_image' => [
        'target_id' => (int) $file->id(),
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

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
  protected function assertStakeholderOrganisationRendering(NodeElement $rendered_stakeholder_element, $name): void {
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
   * Gets the entity type's storage.
   *
   * @param string $entity_type_id
   *   The entity type ID to get a storage for.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The entity type's storage.
   */
  protected function getStorage($entity_type_id): EntityStorageInterface {
    return \Drupal::entityTypeManager()->getStorage($entity_type_id);
  }

}
