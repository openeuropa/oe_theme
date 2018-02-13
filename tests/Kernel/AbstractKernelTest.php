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
   * {@inheritdoc}
   */
  public static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    \Drupal::service('theme_installer')->install(['oe_theme']);
    \Drupal::service('theme_handler')->setDefault('oe_theme');
  }

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
