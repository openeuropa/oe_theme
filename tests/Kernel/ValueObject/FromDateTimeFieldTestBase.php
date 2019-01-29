<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FromDateTimeFieldTestBase.
 *
 * @package Drupal\Tests\oe_theme\Kernel\ValueObject
 */
abstract class FromDateTimeFieldTestBase extends FieldKernelTestBase {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Data provider for rendering test.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function renderingDataProvider(): array {
    return Yaml::parse(file_get_contents(__DIR__ . '/../fixtures/patterns/date_block_pattern_rendering.yml'));
  }

  /**
   * Get fixture content.
   *
   * @return array
   *   Data provider for factory methods test.
   */
  public function dataProviderForFactory(): array {
    return Yaml::parse(file_get_contents(__DIR__ . '/../../Unit/fixtures/value_object/date_value_object.yml'));
  }

}
