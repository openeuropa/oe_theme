<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_theme\Traits\FunctionalJavascriptTrait;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;
use PHPUnit\Framework\Assert;

/**
 * Tests the Javascript behaviours of the theme.
 *
 * @group batch5
 */
class JavascriptBehavioursTest extends WebDriverTestBase {

  use CachedDatabaseInstallTrait;
  use FunctionalJavascriptTrait;

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
  protected function tearDown(): void {
    $this->failOnJavascriptErrors();

    parent::tearDown();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Should be removed after Drupal 10.0.
   */
  protected function drupalGet($path, array $options = [], array $headers = []) {
    $out = parent::drupalGet($path, $options, $headers);
    $this->failOnJavascriptErrors();
    return $out;
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
    $this->getSession()->getPage()->find('css', 'button[data-ecl-label-expanded="Dropdown 0"]')->press();

    // Assert extra attributes and extra classes of the submit button.
    $this->assertSession()->elementExists('css', 'button.ecl-button.ecl-button--primary.button.js-form-submit.form-submit[type="submit"][data-drupal-selector="edit-add-more"][id="edit-add-more"][name="add_more"][value="Add another"][data-once="drupal-ajax"]');
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
    // First dropdown wasn't closed.
    $this->assertSession()->pageTextContains('Child link 0');
    // Close the first dropdown.
    $this->getSession()->getPage()->find('css', 'button[data-ecl-label-expanded="Dropdown 0"]')->press();
    $this->assertSession()->pageTextNotContains('Child link 0');
    $this->assertSession()->pageTextContains('Child link 1');
    // Close the second dropdown.
    $this->getSession()->getPage()->find('css', 'button[data-ecl-label-expanded="Dropdown 1"]')->press();
    $this->assertSession()->pageTextNotContains('Child link 0');
    $this->assertSession()->pageTextNotContains('Child link 1');
  }

  /**
   * Tests that ECL multi select is rendered properly.
   */
  public function testEclMultiSelect(): void {
    $this->drupalGet('/oe_theme_js_test/multi_select');
    // Assert select container.
    $select_container = $this->assertSession()->elementExists('css', 'div.ecl-select__multiple div.ecl-select__container.ecl-select__container--m');
    // Assert the default input is present and shows a default placeholder.
    $select_input = $select_container->find('css', 'button.ecl-select__multiple-toggle');
    $this->assertTrue($this->getSession()->getDriver()->isVisible($select_input->getXpath()));
    $this->assertEquals('Select', $select_input->getText());

    // Assert the select dropdown is hidden.
    $select_dropdown = $this->getSession()->getPage()->find('css', 'div.ecl-select__multiple-dropdown.ecl-select__container.ecl-select__container--m');
    Assert::assertFalse($this->getSession()->getDriver()->isVisible($select_dropdown->getXpath()));

    // Assert the label is present and it points to the right id.
    $form_item = $this->getSession()->getPage()->find('css', 'div.form-item-multi-select');
    $form_label = $form_item->find('css', 'label');
    $this->assertEquals('edit-multi-select', $form_label->getAttribute('for'));

    // Assert the form group does not have an ID taken from the <select>.
    $form_group = $form_item->find('css', '.ecl-form-group');
    $form_select = $form_group->find('css', 'select');
    $this->assertNotEquals($form_select->getAttribute('id'), $form_group->getAttribute('id'));

    // Click the input and assert the dropdown is now visible.
    $select_input->click();
    $select_dropdown = $this->getSession()->getPage()->find('css', 'div.ecl-select__multiple-dropdown.ecl-select__container.ecl-select__container--m');
    $this->assertTrue($this->getSession()->getDriver()->isVisible($select_dropdown->getXpath()));

    // Assert all options are visible.
    $options = [
      'Select all (4)',
      'One',
      'Two point one',
      'Two point two',
      'Three',
    ];
    $option_elements = $select_dropdown->findAll('css', 'div.ecl-checkbox');
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

    // Assert we have two hidden datepicker dialog elements on the page.
    $datepickers = $this->getSession()->getPage()->findAll('css', 'div.ecl-datepicker');
    $datepicker_dialogs = $this->getSession()->getPage()->findAll('css', '.duet-date__dialog');
    $this->assertCount(2, $datepickers);
    $this->assertCount(2, $datepicker_dialogs);
    foreach ($datepicker_dialogs as $datepicker_dialog) {
      $this->assertFalse($datepicker_dialog->isVisible());
    }

    // Assert the first date picker.
    $this->assertEquals('DD-MM-YYYY', $datepickers[0]->getAttribute('data-placeholder'));
    $this->assertEquals('DD-MM-YYYY', $datepickers[0]->find('css', 'input.duet-date__input')->getAttribute('placeholder'));
    $this->assertEmpty($datepickers[0]->find('css', '.duet-date__input-wrapper input[name="test_datepicker_one"]')->getAttribute('value'));
    $this->assertCount(1, $datepickers[0]->findAll('css', 'button.duet-date__toggle'));
    $this->assertTrue($datepickers[0]->find('css', 'duet-date-picker')->hasAttribute('required'));

    // Click the input and assert the datepicker is visible. We can only check
    // the first datepicker because the actual element doesn't have any
    // visible attribute tying it to the input element.
    $datepickers[0]->find('css', 'button.duet-date__toggle')->press();
    $this->assertTrue($datepicker_dialogs[0]->isVisible());
    $this->assertFalse($datepicker_dialogs[1]->isVisible());

    $now = new \DateTime('now', new \DateTimeZone('Europe/Brussels'));

    // Assert datepicker rendering.
    $month_select = $datepickers[0]->find('css', 'select.duet-date__select--month');
    $current_month = $now->format('n');
    $this->assertEquals($current_month - 1, $month_select->getValue());
    $year_select = $datepickers[0]->find('css', 'select.duet-date__select--year');
    $this->assertEquals($now->format('Y'), $year_select->getValue());
    $table = $datepickers[0]->find('css', 'table.duet-date__table');
    $rows = $table->findAll('css', 'tr');
    // Assert days are present - assert only the visually visible strings.
    $headers = $rows['0']->findAll('css', 'th span:not(.duet-date__vhidden)');
    $expected = [
      'MO',
      'TU',
      'WE',
      'TH',
      'FR',
      'SA',
      'SU',
    ];

    foreach ($headers as $key => $column) {
      $this->assertEquals($expected[$key], $column->getText());
    }

    // Pick a date and assert it was set.
    $day = $datepickers[0]->find('css', 'button.duet-date__day.is-month');
    $day->click();
    $this->assertEquals('01-' . $now->format('m-Y'), $datepickers[0]->find('css', 'input.duet-date__input')->getValue());
    // Give the datepicker a chance to hide.
    sleep(1);
    $this->assertFalse($datepicker_dialogs[0]->isVisible());

    // Assert some small differences on the second date input element.
    $this->assertEquals('DD-MM-YYYY', $datepickers[1]->find('css', 'input.duet-date__input')->getAttribute('placeholder'));
    $this->assertStringContainsString('2020-05-10', $datepickers[1]->find('css', '.duet-date__input-wrapper input[name="test_datepicker_two"]')->getAttribute('value'));
    $this->assertCount(1, $datepickers[1]->findAll('css', 'button.duet-date__toggle'));
    $this->assertFalse($datepickers[1]->find('css', 'duet-date-picker')->hasAttribute('required'));

    // Submit the form.
    $this->getSession()->getPage()->pressButton('Submit');
    $this->assertSession()->pageTextContains('Date 0 is 1 ' . $now->format('F Y'));
    $this->assertSession()->pageTextContains('Date 1 is 10 May 2020');
  }

  /**
   * Test ECL tooltip.
   */
  public function testEclTooltip(): void {
    $this->drupalGet('/oe_theme_js_test/tooltip');

    $test_cases = [
      'textfield normal' => '#edit-textfield-normal[data-ecl-tooltip]',
      'textfield inverted' => '#edit-textfield-inverted[data-ecl-tooltip-inverted]',
      'textarea normal' => '#edit-textarea-normal[data-ecl-tooltip]',
      'textarea inverted' => '#edit-textarea-inverted[data-ecl-tooltip-inverted]',
      'email normal' => '#edit-email-normal[data-ecl-tooltip]',
      'email inverted' => '#edit-email-inverted[data-ecl-tooltip-inverted]',
      'number normal' => '#edit-number-normal[data-ecl-tooltip]',
      'number inverted' => '#edit-number-inverted[data-ecl-tooltip-inverted]',
      'select normal' => '#edit-select-normal[data-ecl-tooltip]',
      'select inverted' => '#edit-select-inverted[data-ecl-tooltip-inverted]',
      'submit normal' => '#edit-submit-normal[data-ecl-tooltip]',
      'submit inverted' => '#edit-submit-inverted[data-ecl-tooltip-inverted]',
    ];

    foreach ($test_cases as $label => $selector) {
      $element = $this->getSession()->getPage()->find('css', $selector);
      $this->assertNotNull($element, sprintf('Element "%s" was not found.', $label));

      $expected_tooltip = $element->getAttribute('data-ecl-tooltip') ?: $element->getAttribute('data-ecl-tooltip-inverted');
      $this->assertNotEmpty($expected_tooltip, sprintf('Tooltip text for "%s" was not found.', $label));

      // Custom mouseover event dispatch is needed as the WebDriver mouseover
      // action does not trigger the tooltip visibility in this case.
      $script = <<<JS
      (function () {
        const el = document.querySelector('$selector');
        if (!el) {
          return;
        }
        el.dispatchEvent(new MouseEvent('mouseover', {
          bubbles: true,
          cancelable: true,
          view: window
        }));
      })();
    JS;

      $this->getSession()->executeScript($script);
      $this->assertSession()->waitForElementVisible('css', '.ecl-tooltip');

      $visible = FALSE;

      foreach ($this->getSession()->getPage()->findAll('css', '.ecl-tooltip') as $tooltip) {
        if (
          $this->getSession()->getDriver()->isVisible($tooltip->getXpath()) &&
          trim($tooltip->getText()) === trim($expected_tooltip)
        ) {
          $visible = TRUE;
          break;
        }
      }

      $this->assertTrue($visible, sprintf('Tooltip "%s" is not visible after hover on "%s".', $expected_tooltip, $label));
    }
  }

}
