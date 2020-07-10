<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that our Project content type renders correctly.
 */
class ContentProjectRenderTest extends BrowserTestBase {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'composite_reference',
    'config',
    'datetime_range',
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
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    $this->container->get('plugin.manager.ui_patterns')->clearCachedDefinitions();

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
  }

  /**
   * Tests that the Project page renders the top group correctly.
   */
  public function testProjectTopGroupRender(): void {
    // Create a document for Project results.
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test.pdf');
    $file->setPermanent();
    $file->save();

    $media = $this->container->get('entity_type.manager')->getStorage('media')->create([
      'bundle' => 'document',
      'name' => 'Test document',
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
      ],
      'uid' => 0,
      'status' => 1,
    ]);

    $media->save();

    // Create a Project node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->nodeStorage->create([
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
    $node->save();
    $this->drupalGet($node->toUrl());
    $description_lists = $project_details->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal.ecl-description-list--featured');
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
  }

}
