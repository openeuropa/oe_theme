<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme_helper\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\filter\Entity\FilterFormat;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Test Inpage navigation in the content row paragraph.
 *
 * @group batch3
 */
class InPageNavigationParagraphTest extends WebDriverTestBase {

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

    // Create the main content row paragraph with the inpage navigation variant.
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
    $containers = $this->getSession()->getPage()->findAll('css', '.inpage-navigation-container');
    $this->assertCount(1, $containers);
    $inpage_nav_expected_values = [
      'title' => 'Page navigation',
      'list' => [
        ['label' => 'Rich text title', 'href' => '#rich-text-title'],
        ['label' => 'Here is a heading', 'href' => '#here-is-a-heading'],
      ],
    ];
    $this->assertInPageNavigation($inpage_nav_expected_values, $containers[0]);

    // Create another content row with a rich text paragraph.
    $paragraph3 = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_title' => 'Second rich text paragraph',
      'field_oe_text_long' => [
        'value' => 'Some other text. <h2>Another heading</h2>',
        'format' => 'full_html',
      ],
    ]);
    $paragraph3->save();
    $content_row2 = Paragraph::create([
      'type' => 'oe_content_row',
      'oe_paragraphs_variant' => 'inpage_navigation',
      'field_oe_title' => 'Second page navigation',
      'field_oe_paragraphs' => [$paragraph3],
    ]);
    $content_row->save();

    // Reference the two content rows and the extra rich text paragraphs.
    $node->set('field_oe_demo_body', [
      $content_row,
      $paragraph2,
      $content_row2,
    ]);
    $node->save();

    $this->drupalGet($node->toUrl());
    $containers = $this->getSession()->getPage()->findAll('css', '.inpage-navigation-container');
    $this->assertCount(2, $containers);
    // The first inpage navigation didn't change.
    $this->assertInPageNavigation($inpage_nav_expected_values, $containers[0]);
    $this->assertInPageNavigation([
      'title' => 'Second page navigation',
      'list' => [
        [
          'label' => 'Second rich text paragraph',
          'href' => '#second-rich-text-paragraph',
        ],
        [
          'label' => 'Another heading',
          'href' => '#another-heading',
        ],
      ],
    ], $containers[1]);
  }

  /**
   * Asserts the inpage navigation inside a container.
   *
   * @param array $expected
   *   The expected values.
   * @param \Behat\Mink\Element\NodeElement $container
   *   The inpage navigation container.
   */
  protected function assertInPageNavigation(array $expected, NodeElement $container): void {
    $navigation = $this->assertSession()->elementExists('css', '.ecl-col-l-3 .ecl-inpage-navigation', $container);
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_assert->assertPattern($expected, $navigation->getOuterHtml());
  }

}
