<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

/**
 * Base class for multilingual tests.
 */
abstract class MultilingualAbstractKernelTestBase extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'content_translation',
    'locale',
    'language',
    'oe_multilingual',
    'oe_multilingual_demo',
    'system',
    'user',
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
    'image',
    'breakpoint',
    'responsive_image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('locale', [
      'locales_location',
      'locales_source',
      'locales_target',
    ]);

    $this->installConfig([
      'locale',
      'language',
      'content_translation',
      'oe_multilingual',
      'image',
      'responsive_image',
    ]);

    $this->container->get('module_handler')->loadInclude('oe_multilingual', 'install');
    oe_multilingual_install();

    // Rebuild the container in order to make sure tests pass.
    // @todo: fix test setup so that we can get rid of this line.
    $this->container->get('kernel')->rebuildContainer();
  }

}
