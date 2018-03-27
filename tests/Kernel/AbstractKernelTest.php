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
  public static $modules = [
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

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Get fixture content.
   *
   * @param string $filepath
   *   File path.
   *
   * @return array
   *   A set of test data.
   */
  protected function getFixtureContent($filepath): array {
    return Yaml::parse(file_get_contents(__DIR__ . "/../fixtures/{$filepath}"));
  }

  /**
   * Renders final HTML given a structured array tree.
   *
   * @param array $elements
   *   The structured array describing the data to be rendered.
   *
   * @return string
   *   The rendered HTML.
   *
   * @throws \Exception
   *   When called from inside another renderRoot() call.
   *
   * @see \Drupal\Core\Render\RendererInterface::render()
   */
  protected function renderRoot(array &$elements): string {
    return (string) $this->container->get('renderer')->renderRoot($elements);
  }

}
