<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_inpage_navigation\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\media\MediaInterface;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;

/**
 * Test Inpage navigation block plugin.
 */
class InPageNavigationBlockTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'oe_theme_helper',
    'oe_theme_content_publication',
    'oe_theme_content_organisation',
    'oe_theme_content_organisation_reference',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);

    // Enable test module with placed block in new region and overriden
    // legacy inpage navigation template.
    $this->container->get('module_installer')
      ->install(['oe_theme_inpage_navigation_test']);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

    \Drupal::service('emr.installer')->installEntityMetaTypeOnContentEntityType('oe_theme_inpage_navigation', 'node', ['oe_publication']);

    // Enable inpage_navigation for Publication content.
    $ct_with = NodeType::load('oe_publication');
    $ct_with->setThirdPartySetting('oe_theme_inpage_navigation', 'enabled', TRUE);
    $ct_with->save();
  }

  /**
   * Test in-page entity meta relation plugin.
   */
  public function testInPageNavigationForm(): void {

    // Create a document for Publication.
    $media_document = $this->createMediaDocument('publication_document');

    // Create a Publication node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->create([
        'type' => 'oe_publication',
        'title' => 'Test Publication node',
        'oe_publication_type' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
        'oe_documents' => [$media_document],
        'oe_publication_date' => [
          'value' => '2020-04-15',
        ],
        'oe_subject' => 'http://data.europa.eu/uxp/1000',
        'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
        'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
        'uid' => 0,
        'status' => 1,
      ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert content part.
    $this->assertSession()->elementExists('css', 'main#main-content div.ecl-col-sm-12 div.ecl-row.ecl-u-mt-l div.oe-theme-content-region.ecl-col-lg-9 #block-oe-theme-main-page-content article');

    // Assert in-page navigation part.
    $navigation = $this->assertSession()
      ->elementExists('css', 'main#main-content div.ecl-col-sm-12 div.ecl-row.ecl-u-mt-l div.oe-theme-left-sidebar.ecl-col-12.ecl-col-lg-3  nav.ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Files', 'href' => '#files'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Add body field to node and check in-page navigation links.
    $node->set('body', 'Publication description text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
      ['label' => 'Description', 'href' => '#description'],
      ['label' => 'Files', 'href' => '#files'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Remove body field value of node and check in-page navigation links.
    $node->set('body', NULL);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
      ['label' => 'Files', 'href' => '#files'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Remove body field value of node and check in-page navigation links.
    $node->set('oe_documents', NULL);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Disable inpage_navigation for Publication content.
    $ct_with = NodeType::load('oe_publication');
    $ct_with->setThirdPartySetting('oe_theme_inpage_navigation', 'enabled', FALSE);
    $ct_with->save();
    $this->drupalGet($node->toUrl());

    // Assert absence of in-page navigation block.
    $this->assertSession()->elementNotExists('css', 'main#main-content div.ecl-col-sm-12 div.ecl-row.ecl-u-mt-l div.oe-theme-left-sidebar.ecl-col-12.ecl-col-lg-3  nav.ecl-inpage-navigation');
    // Assert content part.
    $this->assertSession()->elementExists('css', 'main#main-content div.ecl-col-sm-12 > #block-oe-theme-main-page-content article');
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

    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->create([
        'bundle' => 'document',
        'name' => "Test document $name",
        'oe_media_file_type' => 'local',
        'oe_media_file' => [
          'target_id' => (int) $file->id(),
        ],
        'uid' => 0,
        'status' => 1,
      ]);
    $media->save();

    return $media;
  }

}
