<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\ui_patterns_library\Controller\PatternsLibraryController;

/**
 * Class ComponentsPageTest.
 *
 * Purpose of this test class is to make sure
 * that all patterns will work without fatal errors with minimal dependencies.
 */
class ComponentsPageTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['system']);
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Test a components page rendering.
   *
   * @throws \Exception
   */
  public function testComponentsPageRendering(): void {
    $controller = new PatternsLibraryController($this->container->get('plugin.manager.ui_patterns'));
    $renderable_array = $controller->overview();
    $this->container->get('renderer')->renderRoot($renderable_array);
  }

}
