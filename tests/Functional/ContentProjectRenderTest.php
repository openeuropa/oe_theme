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
    // Create a Project node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->nodeStorage->create([
      'type' => 'oe_project',
      'title' => 'Test project node',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
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
    $values = $description_lists[1]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(3, $values);
    $this->assertEquals('€100', $values[0]->getText());
    $this->assertEquals('€100', $values[1]->getText());
    $this->assertEquals('100% of the overall budget', $values[2]->getText());

    // Change EU contribution and assert percentage field change.
    $node->set('oe_project_budget_eu', 50);
    $node->save();

    $this->drupalGet($node->toUrl());

    $description_lists = $project_details->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal.ecl-description-list--featured');
    $values = $description_lists[1]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertEquals('€50', $values[1]->getText());
    $this->assertEquals('50% of the overall budget', $values[2]->getText());

    // Assert the third description list block's labels and values.
    $labels = $description_lists[2]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(1, $labels);
    $this->assertEquals('Project website', $labels[0]->getText());
    $values = $description_lists[2]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(1, $values);
    $values[0]->hasLink('Example website');
  }

}
