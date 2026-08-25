<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

/**
 * Tests rendering of elements (fourth quarter of the fixtures).
 *
 * @group batch7
 */
class RenderingFourTest extends RenderingTestBase {

  /**
   * {@inheritdoc}
   */
  public static function renderingDataProvider(): array {
    return self::getRenderingCasesSlice(4, 4);
  }

}
