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
  public function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();
  }

  /**
   * Test the inpage navigation JS library functionality.
   */
  public function testLibrary(): void {

    $this->drupalGet('/oe-theme-inpage-navigation-test/content');
    $assert_session = $this->assertSession();
    $container = $assert_session->elementExists('css', '#inpage-navigation-test-container');

    // Verify that IDs are added to the correct elements inside the test
    // container.
    // CSS selector uses "descendent-or-self" as prefix, so we need to use
    // XPath to exclude the container from the selection.
    $this->assertCount(11, $container->findAll('xpath', '//*[@id]'));

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

    // Last 4 assertions for the ID generation.
    $this->assertEquals('strip--unwanted-characters', $container->find('css', '.strip')->getAttribute('id'));
    $this->assertEquals('constructor', $container->find('xpath', '/h3[@class="heading"][text()="Reserved keyword"]')->getAttribute('id'));
    $this->assertEquals('length', $container->find('xpath', '/h3[@class="heading"][text()="Length"]')->getAttribute('id'));
    // The heading starting with a non-alpha character gets a ref- prepended.
    $this->assertEquals('ref-2022-a-new-year', $container->find('xpath', '/h3[@class="heading"][text()="2022, a new year"]')->getAttribute('id'));

    $navigation = $assert_session->elementExists('css', '.oe-theme-ecl-inpage-navigation');

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
        [
          'label' => '2022, a new year',
          'href' => '#ref-2022-a-new-year',
        ],
      ],
    ];
    $assert->assertPattern($expected, $navigation->getOuterHtml());

    $this->drupalGet('/oe-theme-inpage-navigation-test/no-entries');
    // Give time for the javascript code to remove the block from the page.
    $assert_session->waitForElementRemoved('css', '#block-inpage-navigation');
    // Ensure that overridden callback is triggered for empty navigation list.
    $assert_session->elementExists('css', 'h1.ecl-page-header__title.empty-inpage-nav-test');
  }

  /**
   * Tests when multiple inpage navigation containers are present in the page.
   */
  public function testMultipleContainers(): void {
    $this->drupalGet('/oe-theme-inpage-navigation-test/multiple');
    $assert_session = $this->assertSession();

    $main_content = $assert_session->elementExists('css', '#inpage-navigation-test-container');
    // Each inpage navigation adds three extra IDs, one for the title, one for
    // a button and one for the ul itself. This means that we are actually
    // expecting 10 IDs generated in total by the library.
    $this->assertCount(14, $main_content->findAll('xpath', '//*[@id]'));

    $first_container = $main_content->find('css', '.first-container');
    $navigation = $assert_session->elementExists('css', '.oe-theme-ecl-inpage-navigation', $first_container);
    $assert = new InPageNavigationAssert();
    $expected = [
      'title' => 'First nav',
      'list' => [
        [
          'label' => 'Area 1 title 1',
          'href' => '#area-1-title-1',
        ],
        [
          'label' => 'Area 1 title 2',
          'href' => '#area-1-title-2',
        ],
        [
          'label' => 'Details',
          'href' => '#details',
        ],
      ],
    ];
    $assert->assertPattern($expected, $navigation->getOuterHtml());

    $second_container = $main_content->find('css', '.second-container');
    $navigation = $assert_session->elementExists('css', '.oe-theme-ecl-inpage-navigation', $second_container);
    $assert = new InPageNavigationAssert();
    $expected = [
      'title' => 'Second nav',
      'list' => [
        [
          'label' => 'Area 2 title 1',
          'href' => '#area-2-title-1',
        ],
        [
          'label' => 'Area 2 title 2',
          'href' => '#area-2-title-2',
        ],
        [
          'label' => 'Details',
          'href' => '#details-1',
        ],
        [
          'label' => 'Area 2 title 4',
          'href' => '#area-2-title-4',
        ],
        [
          'label' => 'Special title element',
          'href' => '#special-title-element',
        ],
      ],
    ];
    $assert->assertPattern($expected, $navigation->getOuterHtml());
  }

}
