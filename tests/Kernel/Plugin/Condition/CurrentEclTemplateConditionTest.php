<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Plugin\Condition;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test CurrentEclTemplateCondition plugin.
 */
class CurrentEclTemplateConditionTest extends AbstractKernelTestBase {

  /**
   * Tests the current ECL template condition.
   */
  public function testCondition(): void {
    $manager = \Drupal::service('plugin.manager.condition');
    $condition = $manager->createInstance('oe_theme_helper_current_ecl_template');
    $condition->setConfiguration(['ecl_template' => 'standardised']);

    $condition_empty = $manager->createInstance('oe_theme_helper_current_ecl_template');

    $condition_negated = $manager->createInstance('oe_theme_helper_current_ecl_template');
    $condition_negated->setConfiguration(['ecl_template' => 'standardised', 'negate' => TRUE]);

    $this->assertEqual($condition->summary(), new FormattableMarkup('The current ECL template is @ecl_template', ['@ecl_template' => 'standardised']));
    $this->assertEqual($condition_empty->summary(), new FormattableMarkup('The current ECL template can be set to anything', []));
    $this->assertEqual($condition_negated->summary(), new FormattableMarkup('The current ECL template is not @ecl_template', ['@ecl_template' => 'standardised']));

    // Assert condition values, by default the ECL template
    // set to "Standardised".
    $this->assertTrue($condition->execute(), 'Condition asserting that ECL template is "standardised" should be true.');
    $this->assertTrue($condition_empty->execute(), 'Condition that has no ECL template value set should always be true.');
    $this->assertFalse($condition_negated->execute(), 'Condition asserting that ECL template is not "standardised" should be false.');

    // Change ECL template to "Core" and assert new condition execution.
    $this->config('oe_theme.settings')->set('template', 'core')->save();
    $this->assertFalse($condition->execute(), 'Condition asserting that ECL template is "standardised" should be false.');
    $this->assertTrue($condition_empty->execute(), 'Condition that has no ECL template value set should always be true.');
    $this->assertTrue($condition_negated->execute(), 'Condition asserting that ECL template is not "standardised" should be true.');
  }

}
