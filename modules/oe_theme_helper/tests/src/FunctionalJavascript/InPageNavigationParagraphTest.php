<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\FunctionalJavascript;

use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;

/**
 * Test Inpage navigation in the content row paragraph.
 *
 * @group batch3
 */
class InPageNavigationParagraphTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'oe_theme_helper',
    'oe_theme_content_page',
    'oe_paragraphs',
    'oe_paragraphs_demo',
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

    FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
    ])->save();
  }

  /**
   * Tests the inpage nav variant of the content row paragraph type.
   */
  public function testInpageNavigationInParagraphContentRow(): void {
    // Create a rich text paragraph with a title and a heading in the body.
    $paragraph = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_title' => 'Rich text title',
      'field_oe_text_long' => [
        'value' => 'The rich text body. <h2>Here is a heading</h2>',
        'format' => 'full_html',
      ],
    ]);
    $paragraph->save();

    // Create the main content row paragraph with a default variant.
    $content_row = Paragraph::create([
      'type' => 'oe_content_row',
      'oe_paragraphs_variant' => 'inpage_navigation',
      'field_oe_title' => 'Page navigation',
      'field_oe_paragraphs' => [$paragraph],
    ]);
    $content_row->save();

    // Create standalone a rich text paragraph with headings not used
    // by inpage navigation block.
    $paragraph2 = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_title' => 'Rich text title',
      'field_oe_text_long' => [
        'value' => 'The rich text body. <h2>Here is a heading</h2>',
        'format' => 'full_html',
      ],
    ]);
    $paragraph2->save();

    // Create a landing page that uses this paragraph.
    $node = $this->drupalCreateNode([
      'title' => 'The node title',
      'type' => 'oe_demo_landing_page',
      'field_oe_demo_body' => [$content_row, $paragraph2],
    ]);

    $this->drupalGet($node->toUrl());
    $navigation = $this->assertSession()->elementExists('css', '.ecl-col-l-3.ecl-u-z-navigation .ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page navigation',
      'list' => [
        ['label' => 'Rich text title', 'href' => '#rich-text-title'],
        ['label' => 'Here is a heading', 'href' => '#here-is-a-heading'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

  }

}
