<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

/**
 * Tests rendering of elements (third third of the fixtures).
 *
 * @group batch3
 */
class RenderingThreeTest extends RenderingTestBase {

  /**
   * {@inheritdoc}
   */
  public static function renderingDataProvider(): array {
    return self::getRenderingCasesSlice(3, 3);
  }

}
