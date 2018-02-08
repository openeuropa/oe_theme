<?php

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractKernelTest.
 *
 * @package Drupal\Tests\oe_theme\Kernel
 */
abstract class AbstractKernelTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system'];

  /**
   * Get fixture content.
   *
   * @param string $filepath
   *   File path.
   *
   * @return array
   *   Content.
   */
  protected function getFixtureContent($filepath) {
    return Yaml::parse(file_get_contents(__DIR__ . "/../fixtures/{$filepath}"));
  }

}
