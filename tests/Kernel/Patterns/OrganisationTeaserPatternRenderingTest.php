<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test Organisation teaser pattern rendering.
 *
 * @group batch2
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
   *   An array of test data arrays with assertions.
   */
  public function dataProvider(): array {
    $data = $this->getFixtureContent('patterns/organisation_teaser_rendering.yml');

    foreach ($data as &$item) {
      foreach ($item['fields'] as $field_name => $field_value) {
        if ($field_name === 'logo' && !empty($field_value)) {
          $item['fields'][$field_name] = ImageValueObject::fromArray($field_value);
        }
      }
    }

    return $data;
  }

}
