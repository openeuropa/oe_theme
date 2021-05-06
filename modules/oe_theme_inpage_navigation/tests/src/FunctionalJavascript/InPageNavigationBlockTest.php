<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_inpage_navigation\FunctionalJavascript;

use Drupal\block\Entity\Block;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\media\MediaInterface;
use Drupal\node\Entity\NodeType;
use Drupal\oe_theme_inpage_navigation\InPageNavigationHelper;
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
    'oe_theme_content_page',
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

    // Enable test module with placed block in new region.
    $this->container->get('module_installer')
      ->install(['oe_theme_inpage_navigation_test']);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

    \Drupal::service('emr.installer')->installEntityMetaTypeOnContentEntityType('oe_theme_inpage_navigation', 'node', ['oe_publication', 'oe_page']);

    // Enable inpage_navigation for Publication content.
    $ct_with = NodeType::load('oe_publication');
    $ct_with->setThirdPartySetting('oe_theme_inpage_navigation', 'enabled', TRUE);
    $ct_with->save();
  }

  /**
   * Test content with enabled legacy in-page navigation by default.
   */
  public function testContentWithInPageNav(): void {
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

    // Assert in-page navigation part.
    $navigation = $this->assertSession()->elementExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Details', 'href' => '#details'],
        ['label' => 'Files', 'href' => '#files'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Add body field value to node and check that appeared heading
    // for description section is counted in-page navigation links.
    $node->set('body', 'Publication description text');
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
      ['label' => 'Description', 'href' => '#description'],
      ['label' => 'Files', 'href' => '#files'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Remove documents field value of the node and check that disappearing
    // of the files section is reflected inside in-page navigation block.
    $node->set('oe_documents', NULL);
    $node->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values['list'] = [
      ['label' => 'Details', 'href' => '#details'],
      ['label' => 'Description', 'href' => '#description'],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Disable inpage_navigation for Publication content.
    $ct_with = NodeType::load('oe_publication');
    $ct_with->setThirdPartySetting('oe_theme_inpage_navigation', 'enabled', FALSE);
    $ct_with->save();
    $this->drupalGet($node->toUrl());

    // Assert absence of in-page navigation block.
    $this->assertSession()->elementNotExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
  }

  /**
   * Test content with disabled legacy in-page navigation by default.
   */
  public function testContentWithoutInPageNav(): void {
    // Create a Page node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->create([
        'type' => 'oe_page',
        'title' => 'Test Page node',
        'oe_teaser' => 'Teaser text',
        'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
        'uid' => 0,
        'status' => 1,
      ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    // Assert absence of in-page navigation block.
    $this->assertSession()->elementNotExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');

    InPageNavigationHelper::enableInPageNavigation($node);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Check that in-page navigation block still not visible.
    $this->assertSession()->elementNotExists('css', 'nav[data-ecl-inpage-navigation]');

    // Check that in-page navigation block visible when available
    // heading in content.
    $node->set('oe_related_links', [
      [
        'uri' => 'internal:/node',
        'title' => 'Node listing',
      ],
      [
        'uri' => 'https://example.com',
        'title' => 'External link',
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    // Assert in-page navigation part.
    $navigation = $this->assertSession()->elementExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Related links', 'href' => '#related-links'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Check that in-page navigation block visible when available
    // heading in wysiwyg field.
    $node->set('body', [
      [
        'value' => '<h2>Heading from body field</h2>',
        'format' => 'full_html',
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    // Assert in-page navigation part.
    $navigation = $this->assertSession()->elementExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Heading from body field', 'href' => '#heading-from-body-field'],
        ['label' => 'Related links', 'href' => '#related-links'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Check that in-page navigation block count presence of
    // h2.ecl-u-type-heading-2 elements inside content region.
    $block = Block::create([
      'id' => 'pagetitle',
      'theme' => 'oe_theme',
      'langcode' => 'en',
      'weight' => 0,
      'status' => TRUE,
      'region' => 'content',
      'plugin' => 'page_title_block',
      'provider' => NULL,
      'settings' => [
        'label' => 'Page title',
        'provider' => 'core',
        'label_display' => TRUE,
      ],
      'visibility' => [],
    ]);
    $block->save();
    \Drupal::service('twig')->invalidate();
    $this->drupalGet($node->toUrl());
    // Assert in-page navigation part.
    $navigation = $this->assertSession()->elementExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Test Page node', 'href' => '#test-page-node'],
        ['label' => 'Heading from body field', 'href' => '#heading-from-body-field'],
        ['label' => 'Related links', 'href' => '#related-links'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Check that in-page navigation block is hidden when
    // we don't have anymore heading elements.
    $node->set('oe_related_links', NULL);
    $node->set('body', NULL);
    $node->save();
    $block->delete();
    $this->drupalGet($node->toUrl());
    // Assert absence of in-page navigation block.
    $this->assertSession()->elementNotExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
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
