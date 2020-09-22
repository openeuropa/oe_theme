<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use PHPUnit\Framework\Assert;

/**
 * Tests the Javascript behaviours of the theme.
 *
 * @group oe_theme
 */
class JavascriptBehavioursTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'page_cache',
    'dynamic_page_cache',
    'oe_multilingual',
    'oe_theme_helper',
    'oe_theme_js_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Tests that ECL auto init is invoked and applied correctly.
   */
  public function testEclAutoInit(): void {
    $this->drupalGet('/oe_theme_js_test/ajax_dropdown');

    // Verify that the first dropdown button is shown, and it's collapsed.
    $this->assertSession()->buttonExists('Dropdown 0');
    $this->assertSession()->pageTextNotContains('Child link 0');

    // Click the button to expand the dropdown and see the inner link.
    $this->getSession()->getPage()->pressButton('Dropdown 0');
    $this->assertSession()->pageTextContains('Child link 0');

    // We need to close the dropdown now. Clicking on the container will do.
    $this->getSession()->getPage()->find('css', '#dropdown-container')->click();

    // Add a new dropdown.
    $this->getSession()->getPage()->pressButton('Add another');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Both dropdowns are present and collapsed.
    $this->assertSession()->buttonExists('Dropdown 0');
    $this->assertSession()->pageTextNotContains('Child link 0');
    $this->assertSession()->buttonExists('Dropdown 1');
    $this->assertSession()->pageTextNotContains('Child link 1');

    // Verify that the first dropdown opens correctly.
    $this->getSession()->getPage()->pressButton('Dropdown 0');
    $this->assertSession()->pageTextContains('Child link 0');
    $this->assertSession()->pageTextNotContains('Child link 1');
    // Verify that the JS behaviours initialised ECL on the second dropdown.
    $this->getSession()->getPage()->pressButton('Dropdown 1');
    $this->assertSession()->pageTextContains('Child link 1');
    $this->assertSession()->pageTextNotContains('Child link 0');
  }

  /**
   * Tests that ECL multi select is rendered properly.
   */
  public function testEclMultiSelect(): void {
    $this->drupalGet('/oe_theme_js_test/multi_select');
    // Assert the default input is present and shows a default placeholder.
    $select_input = $this->getSession()->getPage()->find('css', 'input.ecl-select__multiple-toggle');
    Assert::assertTrue($this->getSession()->getDriver()->isVisible($select_input->getXpath()));
    Assert::assertEquals('Select', $select_input->getAttribute('placeholder'));

    // Assert the select dropdown is hidden.
    $select_dropdown = $this->getSession()->getPage()->find('css', 'div.ecl-select__multiple-dropdown');
    Assert::assertFalse($this->getSession()->getDriver()->isVisible($select_dropdown->getXpath()));

    // Click the input and assert the dropdown is now visible.
    $select_input->click();
    $select_dropdown = $this->getSession()->getPage()->find('css', 'div.ecl-select__multiple-dropdown');
    Assert::assertTrue($this->getSession()->getDriver()->isVisible($select_dropdown->getXpath()));

    // Assert all options are visible.
    $options = [
      'Select all',
      'One',
      'Two point one',
      'Two point two',
      'Three',
    ];
    $option_elements = $this->getSession()->getPage()->findAll('css', 'div.ecl-checkbox');
    Assert::assertEquals(count($options), count($option_elements));
    foreach ($options as $index => $option) {
      Assert::assertEquals($option, $option_elements[$index]->getText());
    }
  }

}
