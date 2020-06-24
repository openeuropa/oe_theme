<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test Organisation teaser pattern rendering.
 */
class OrganisationTeaserPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that Organisation teaser pattern is correctly rendered.
   *
   * @param array $fields
   *   Fields data.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider dataProvider
   */
  public function testOrganisationTeaserPatternRendering(array $fields, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'organisation_teaser',
      '#fields' => $fields,
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testOrganisationTeaserPatternRendering.
   *
   * @return array
   *   An array of test data arrays with assertations.
   */
  public function dataProvider(): array {
    return $this->getFixtureContent('patterns/organisation_teaser_rendering.yml');
  }

}
