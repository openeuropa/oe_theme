<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;

/**
 * Test the inpage navigation library.
 *
 * @group batch3
 *
 * @group oe_theme_helper
 */
class InPageNavigationLibraryTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'oe_theme_helper',
    'oe_theme_inpage_navigation_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();
  }

  /**
   * Test the inpage navigation JS library functionality.
   */
  public function testLibrary(): void {
    // Place the block so there's an element that will host the generated items.
    $this->drupalPlaceBlock('oe_theme_helper_inpage_navigation', [
      'region' => 'content',
      'weight' => -10,
      'label_display' => FALSE,
      'id' => 'inpage_navigation',
    ]);

    $this->drupalGet('/oe-theme-inpage-navigation-test/content');
    $assert_session = $this->assertSession();
    $container = $assert_session->elementExists('css', '#inpage-navigation-test-container');

    // Verify that IDs are added to the correct elements inside the test
    // container.
    // CSS selector uses "descendent-or-self" as prefix, so we need to use
    // XPath to exclude the container from the selection.
    $this->assertCount(10, $container->findAll('xpath', '//*[@id]'));

    $inner = $container->find('xpath', '/div[@data-inpage-navigation-source-area="h3"]');
    // Since an element with ID "details" already exists in the page, the
    // counter will start from 1 for this element.
    $this->assertEquals('details-1', $inner->find('xpath', '/h3[@class="heading"][text()="Details"]')->getAttribute('id'));
    $this->assertEquals('multiple-words-with-spaces', $inner->find('xpath', '/h3[text()="Multiple words with spaces "]')->getAttribute('id'));
    // This line covers two cases:
    // - escaped selectors are applied correctly;
    // - selectors from "outside" source areas are applied also in inner areas.
    $this->assertEquals('test-on-attribute-selector', $inner->find('xpath', '/strong[@data-test-attribute="heading"]')->getAttribute('id'));

    // The counter keeps increasing for instances with same slug.
    $this->assertEquals(
      'details-2',
      $container->find('xpath', '/div[@data-inpage-navigation-source-area="h3"]/following-sibling::h3[position() = 1][text()="Details"]')->getAttribute('id')
    );

    // The element where the ID was specified retained it correctly.
    $this->assertEquals('More details', $container->find('xpath', '/h3[@id="details"]')->getText());

    // Test that markup is removed from generated IDs. We search by ID as it's
    // easier to assert the content.
    $this->assertEquals('Title with <strong>HTML tags</strong>', $container->find('xpath', '/h3[@id="title-with-html-tags"]')->getHtml());

    // Last 3 assertions for the ID generation.
    $this->assertEquals('strip--unwanted-characters', $container->find('css', '.strip')->getAttribute('id'));
    $this->assertEquals('constructor', $container->find('xpath', '/h3[@class="heading"][text()="Reserved keyword"]')->getAttribute('id'));
    $this->assertEquals('length', $container->find('xpath', '/h3[@class="heading"][text()="Length"]')->getAttribute('id'));

    $navigation = $assert_session->elementExists('css', '#block-inpage-navigation nav[data-ecl-inpage-navigation]');
    $assert = new InPageNavigationAssert();
    $expected = [
      'title' => 'Page contents',
      'list' => [
        [
          'label' => 'Details',
          'href' => '#details-1',
        ],
        [
          'label' => 'Multiple words with spaces',
          'href' => '#multiple-words-with-spaces',
        ],
        [
          'label' => 'Test on attribute selector.',
          'href' => '#test-on-attribute-selector',
        ],
        [
          'label' => 'Details',
          'href' => '#details-2',
        ],
        [
          'label' => 'More details',
          'href' => '#details',
        ],
        [
          'label' => 'Title with HTML tags',
          'href' => '#title-with-html-tags',
        ],
        [
          'label' => "Strip \u{203F}\'!\"#\$%&()*+,./:;<=>?@[]^`{|}~ unwanted characters",
          'href' => '#strip--unwanted-characters',
        ],
        [
          'label' => 'Reserved keyword',
          'href' => '#constructor',
        ],
        [
          'label' => 'Length',
          'href' => '#length',
        ],
      ],
    ];
    $assert->assertPattern($expected, $navigation->getOuterHtml());

    $this->drupalGet('/oe-theme-inpage-navigation-test/no-entries');
    // Give time for the javascript code to remove the block from the page.
    $assert_session->waitForElementRemoved('css', '#block-inpage-navigation');
    // Ensure that overridden callback is triggered for empty navigation list.
    $assert_session->elementExists('css', 'h1.ecl-page-header-core__title.empty-inpage-nav-test');
  }

}
