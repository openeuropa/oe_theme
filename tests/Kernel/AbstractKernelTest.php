<?php

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\KernelTests\KernelTestBase;

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
  public static $modules = [
    'oe_theme_test',
  ];

  /**
   * Get fixtures base path.
   *
   * @return string
   *   Fixtures base path.
   */
  protected function getFixturePath() {
    return realpath(__DIR__ . '/../fixtures');
  }

}
