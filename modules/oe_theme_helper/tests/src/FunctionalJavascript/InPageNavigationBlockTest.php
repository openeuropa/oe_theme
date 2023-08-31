<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\FunctionalJavascript;

use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;

/**
 * Test Inpage navigation block plugin.
 *
 * @group batch3
 *
 * @group oe_theme_helper
 */
class InPageNavigationBlockTest extends WebDriverTestBase {

  /**
   * Disabled until FRONT-4076 is fixed.
   *
   * {@inheritdoc}
   */
  protected $failOnJavascriptConsoleErrors = FALSE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'oe_theme_helper',
    'oe_theme_content_page',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

    // Enable inpage_navigation block.
    $this->drupalPlaceBlock('oe_theme_helper_inpage_navigation', [
      'region' => 'content',
      'weight' => -10,
      'label_display' => FALSE,
      'id' => 'inpage_navigation',
    ]);

    FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
    ])->save();
  }

  /**
   * Tests that blocks are configured to use h2 in inpage navigation generation.
   */
  public function testContentWithoutInPageNav(): void {
    // Create a Page node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->create([
        'type' => 'oe_page',
        'title' => 'Test Page node',
        'body' => 'Body text',
        'oe_teaser' => 'Teaser text',
        'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
        'uid' => 0,
        'status' => 1,
      ]);

    // Add related links.
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
    // Assert that the “Related link” entry is generated.
    $navigation = $this->assertSession()->elementExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Related links', 'href' => '#related-links'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Add some h2 headers in the body field.
    $node->set('body', [
      [
        'value' => '<h2>Heading from body field</h2>',
        'format' => 'full_html',
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    // Assert h2 are also considered in the in-page navigation.
    $navigation = $this->assertSession()->elementExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        [
          'label' => 'Heading from body field',
          'href' => '#heading-from-body-field',
        ],
        [
          'label' => 'Related links',
          'href' => '#related-links',
        ],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Assert that the in-page navigation block is removed
    // when there are no more h2 in the content area.
    $node->set('oe_related_links', NULL);
    $node->set('body', NULL);
    $node->save();
    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', '#main-content h2');
    $this->assertSession()->elementNotExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
  }

}
