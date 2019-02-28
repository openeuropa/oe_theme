<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Drupal\Tests\oe_theme\Traits\RequestTrait;

/**
 * Tests the page header block functionality.
 */
class PageHeaderTest extends EntityKernelTestBase {

  use RequestTrait;
  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'ui_patterns',
    'oe_theme_helper',
    'oe_theme_page_header_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installConfig(['system']);

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Tests that we can alter the page header block using a subscriber.
   *
   * @param string $context_title
   *   The title to be sent to the context.
   * @param string $expected_title
   *   The expected title upon rendering.
   *
   * @dataProvider pageAlterDataProvider
   */
  public function testPageHeaderAlter(string $context_title, string $expected_title): void {
    /** @var \Drupal\Core\Block\BlockBase $plugin */
    $plugin = $this->container->get('plugin.manager.block')->createInstance('oe_theme_helper_page_header', []);
    $contexts = [
      'page_header' => new Context(new ContextDefinition('map', 'Metadata'), [
        'title' => $context_title,
      ]),
    ];
    $this->container->get('context.handler')->applyContextMapping($plugin, $contexts);
    $build = $plugin->build();
    $this->assertEquals($expected_title, $build['#title']);
  }

  /**
   * Provides different titles to test the alteration of the page header block.
   *
   * @see self::testPageHeaderAlter()
   *
   * @return array
   *   The data
   */
  public function pageAlterDataProvider() {
    return [
      ['My title', 'My title'],
      ['Alter it', 'Altered title'],
    ];
  }

}
