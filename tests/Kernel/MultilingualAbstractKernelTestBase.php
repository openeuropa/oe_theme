<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class MultilingualAbstractKernelTestBase.
 *
 * @package Drupal\Tests\oe_theme\Kernel
 */
abstract class MultilingualAbstractKernelTestBase extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'administration_language_negotiation',
    'content_translation',
    'locale',
    'language',
    'oe_multilingual',
    'oe_multilingual_demo',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installSchema('locale', [
      'locales_location',
      'locales_source',
      'locales_target',
    ]);

    $this->installConfig([
      'locale',
      'language',
      'content_translation',
      'administration_language_negotiation',
      'oe_multilingual',
    ]);

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);

    $this->container->get('module_handler')->loadInclude('oe_multilingual', 'install');
    oe_multilingual_install();
  }

}
