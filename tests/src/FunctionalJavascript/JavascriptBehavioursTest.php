<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use PHPUnit\Framework\Assert;

/**
 * Tests the Javascript behaviours of the theme.
 *
 * @group batch3
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
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
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
    $this->assertTrue($this->getSession()->getDriver()->isVisible($select_input->getXpath()));
    $this->assertEquals('Select', $select_input->getAttribute('placeholder'));

    // Assert the select dropdown is hidden.
    $select_dropdown = $this->getSession()->getPage()->find('css', 'div.ecl-select__multiple-dropdown');
    Assert::assertFalse($this->getSession()->getDriver()->isVisible($select_dropdown->getXpath()));

    // Click the input and assert the dropdown is now visible.
    $select_input->click();
    $select_dropdown = $this->getSession()->getPage()->find('css', 'div.ecl-select__multiple-dropdown');
    $this->assertTrue($this->getSession()->getDriver()->isVisible($select_dropdown->getXpath()));

    // Assert all options are visible.
    $options = [
      'Select all',
      'One',
      'Two point one',
      'Two point two',
      'Three',
    ];
    $option_elements = $this->getSession()->getPage()->findAll('css', 'div.ecl-checkbox');
    $this->assertEquals(count($options), count($option_elements));
    foreach ($options as $index => $option) {
      $this->assertEquals($option, $option_elements[$index]->getText());
    }
  }

  /**
   * Tests that ECL datepicker is rendered properly.
   */
  public function testEclDatePicker(): void {
    $this->drupalGet('/oe_theme_js_test/datepicker');

    // Assert we have two hidden datepicker elements on the page.
    $datepickers = $this->getSession()->getPage()->findAll('css', 'div.ecl-datepicker-theme');
    $this->assertCount(2, $datepickers);
    foreach ($datepickers as $datepicker) {
      $this->assertFalse($datepicker->isVisible());
    }

    // Assert the first date picker.
    $input = $this->getSession()->getPage()->find('css', 'input[name="test_datepicker_one"]');
    $this->assertEquals('YYYY-MM-DD', $input->getAttribute('placeholder'));
    $this->assertNull($input->getAttribute('value'));
    $this->assertTrue($input->hasAttribute('data-ecl-datepicker-toggle'));
    $this->assertTrue($input->hasAttribute('required'));
    $icon = $this->getSession()->getPage()->find('css', '.form-item-test-datepicker-one .ecl-datepicker .ecl-icon');
    $this->assertStringContainsString('icons.svg#calendar', $icon->getHtml());

    // Click the input and assert the datepicker is visible. We can only check
    // the first datepicker because the actual element doesn't have any
    // visible attribute tying it to the input element.
    $input->click();
    $this->assertTrue($datepickers[0]->isVisible());
    $this->assertFalse($datepickers[1]->isVisible());

    $now = new \DateTime('now', new \DateTimeZone('Europe/Brussels'));

    // Assert datepicker rendering.
    $month_select = $datepickers[0]->find('css', 'select.pika-select-month');
    $current_month = $now->format('n');
    $this->assertEquals($current_month - 1, $month_select->getValue());
    $year_select = $datepickers[0]->find('css', 'select.pika-select-year');
    $this->assertEquals($now->format('Y'), $year_select->getValue());
    $table = $datepickers[0]->find('css', 'table.pika-table');
    $rows = $table->findAll('css', 'tr');
    // Assert days are present.
    $headers = $rows['0']->findAll('css', 'th');
    $expected = [
      'Mon',
      'Tue',
      'Wed',
      'Thu',
      'Fri',
      'Sat',
      'Sun',
    ];

    foreach ($headers as $key => $column) {
      $this->assertEquals($expected[$key], $column->getText());
    }

    // Pick a date and assert it was set.
    $day = $datepickers[0]->find('css', 'button[data-pika-day=1]');
    $day->click();
    $this->assertEquals($now->format('Y-m') . '-01', $input->getValue());
    // Give the datepicker a chance to hide.
    sleep(1);
    $this->assertFalse($datepickers[0]->isVisible());

    // Assert some small differences on the second date input element.
    $input = $this->getSession()->getPage()->find('css', 'input[name="test_datepicker_two"]');
    $this->assertEquals('YYYY-MM-DD', $input->getAttribute('placeholder'));
    $this->assertStringContainsString('2020-05-10', $input->getAttribute('value'));
    $this->assertTrue($input->hasAttribute('data-ecl-datepicker-toggle'));
    $this->assertFalse($input->hasAttribute('required'));
    $icon = $this->getSession()->getPage()->find('css', '.form-item-test-datepicker-two .ecl-datepicker .ecl-icon');
    $this->assertStringContainsString('icons.svg#calendar', $icon->getHtml());

    // Submit the form.
    $this->getSession()->getPage()->pressButton('Submit');
    $this->assertSession()->pageTextContains('Date 0 is 1 ' . $now->format('F Y'));
    $this->assertSession()->pageTextContains('Date 1 is 10 May 2020');
  }

  /**
   * Tests that contextual navigation pattern is rendered properly.
   */
  public function testContextNavPattern(): void {
    $this->drupalGet('/oe_theme_js_test/ui_patterns/context_nav');
    $script = <<<EndOfScript
(function(){
if (typeof(window.collectedErrors) == 'undefined') {
  return;
}
var result = '';
window.collectedErrors.forEach(function(value) {
   result += value.data + '[NLS]';
});
return result;
})()
EndOfScript;
    $errors = $this->getSession()->evaluateScript($script);
    if ($errors) {
      throw new \RuntimeException('Javascript error: ' . str_replace('[NLS]', PHP_EOL, $errors));
    }

    $this->assertCount(2, $this->getSession()->getPage()->findAll('css', '[data-ecl-contextual-navigation-list]'));
    $this->assertCount(1, $this->getSession()->getPage()->findAll('css', '[data-ecl-contextual-navigation-more]'));

    $this->getSession()->getPage()->pressButton('More label');
    $this->assertCount(0, $this->getSession()->getPage()->findAll('css', '[data-ecl-contextual-navigation-more]'));
    $this->assertCount(2, $this->getSession()->getPage()->findAll('css', '[data-ecl-contextual-navigation-list]'));
  }

}
