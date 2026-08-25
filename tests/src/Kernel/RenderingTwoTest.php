<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

/**
 * Tests rendering of elements (second quarter of the fixtures).
 *
 * @group batch5
 */
class RenderingTwoTest extends RenderingTestBase {

  /**
   * {@inheritdoc}
   */
  public static function renderingDataProvider(): array {
    return self::getRenderingCasesSlice(2, 4);
  }

}
