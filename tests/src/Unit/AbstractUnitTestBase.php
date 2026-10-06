<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Unit;

use Drupal\Component\Serialization\Yaml;
use Drupal\Tests\UnitTestCase;

/**
 * Base class for theme's unit tests.
 */
abstract class AbstractUnitTestBase extends UnitTestCase {

  /**
   * Get fixture content.
   *
   * @param string $filepath
   *   File path.
   *
   * @return array
   *   A set of test data.
   */
  protected static function getFixtureContent(string $filepath): array {
    return Yaml::decode(file_get_contents(__DIR__ . "/fixtures/{$filepath}"));
  }

}
