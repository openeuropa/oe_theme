<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests implementation of site header ECL component.
 */
class SiteHeaderTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'block',
    'user',
    'ui_patterns',
    'ui_patterns_library',
    'oe_search',
    'oe_theme_demo',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme', 'oe_theme_subtheme_test']);
  }

  /**
   * Test that the correct layout is used after changing theme template setting.
   */
  public function testChangeEclTemplate(): void {
    foreach (['oe_theme', 'oe_theme_subtheme_test'] as $active_theme) {
      $this->container->get('theme_handler')->setDefault($active_theme);
      $this->container->set('theme.registry', NULL);

      $page = $this->getSession()->getPage();
      $assert_session = $this->assertSession();

      $this->drupalGet('<front>');

      print_r($this->getSession()->getPage()->getHtml());
      break;
    }
  }

}
