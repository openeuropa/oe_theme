<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit;

use Symfony\Component\Yaml\Yaml;
use Drupal\Tests\UnitTestCase;

/**
 * Class AbstractUnitTestBase.
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
  protected function getFixtureContent(string $filepath): array {
    return Yaml::parse(file_get_contents(__DIR__ . "/fixtures/{$filepath}"));
  }

}
