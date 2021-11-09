<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Plugin\Condition;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test CurrentEclBrandingCondition plugin.
 *
 * @group batch2
 */
class CurrentEclBrandingConditionTest extends AbstractKernelTestBase {

  /**
   * Tests the current ECL branding condition.
   */
  public function testCondition(): void {
    $manager = \Drupal::service('plugin.manager.condition');
    $condition = $manager->createInstance('oe_theme_helper_current_branding');
    $condition->setConfiguration(['branding' => 'standardised']);

    $condition_empty = $manager->createInstance('oe_theme_helper_current_branding');

    $condition_negated = $manager->createInstance('oe_theme_helper_current_branding');
    $condition_negated->setConfiguration([
      'branding' => 'standardised',
      'negate' => TRUE,
    ]);

    $this->assertEquals(new FormattableMarkup('The current ECL branding is @branding', ['@branding' => 'standardised']), $condition->summary());
    $this->assertEquals(new FormattableMarkup('The current ECL branding can be set to anything', []), $condition_empty->summary());
    $this->assertEquals(new FormattableMarkup('The current ECL branding is not @branding', ['@branding' => 'standardised']), $condition_negated->summary());

    // Assert condition values, by default ECL branding set to "Standardised".
    $this->assertFalse($condition->execute(), 'Condition asserting that ECL branding is "standardised" should be false.');
    $this->assertTrue($condition_empty->execute(), 'Condition that has no ECL branding value set should always be true.');
    $this->assertTrue($condition_negated->execute(), 'Condition asserting that ECL branding is not "standardised" should be true.');

    // Change ECL branding to "Core" and assert new condition execution.
    $this->config('oe_theme.settings')->set('branding', 'standardised')->save();
    $this->assertTrue($condition->execute(), 'Condition asserting that ECL branding is "standardised" should be true.');
    $this->assertTrue($condition_empty->execute(), 'Condition that has no ECL branding value set should always be true.');
    $this->assertFalse($condition_negated->execute(), 'Condition asserting that ECL branding is not "standardised" should be false.');
  }

}
