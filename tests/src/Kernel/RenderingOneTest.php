<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

/**
 * Tests rendering of elements (first quarter of the fixtures).
 *
 * @group batch4
 */
class RenderingOneTest extends RenderingTestBase {

  /**
   * {@inheritdoc}
   */
  public static function renderingDataProvider(): array {
    return self::getRenderingCasesSlice(1, 4);
  }

}
