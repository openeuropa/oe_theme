<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractKernelTestBase.
 *
 * @package Drupal\Tests\oe_theme\Kernel
 */
abstract class AbstractKernelTestBase extends KernelTestBase {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['system']);

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
  protected function getFixtureContent(string $filepath): array {
    return Yaml::parse(file_get_contents(__DIR__ . "/fixtures/{$filepath}"));
  }

  /**
   * Builds and returns the renderable array for a block.
   *
   * @param string $block_id
   *   The ID of the block.
   * @param array $config
   *   An array of configuration.
   *
   * @return array
   *   A renderable array representing the content of the block.
   */
  protected function buildBlock(string $block_id, array $config): array {
    /** @var \Drupal\Core\Block\BlockBase $plugin */
    $plugin = $this->container->get('plugin.manager.block')->createInstance($block_id, $config);

    // Inject runtime contexts.
    if ($plugin instanceof ContextAwarePluginInterface) {
      $contexts = $this->container->get('context.repository')->getRuntimeContexts($plugin->getContextMapping());
      $this->container->get('context.handler')->applyContextMapping($plugin, $contexts);
    }

    return $plugin->build();
  }

  /**
   * Run various assertion on given HTML string via CSS selectors.
   *
   * Specifically:
   *
   * - 'count': assert how many times the given HTML elements occur.
   * - 'equals': assert content of given HTML elements.
   * - 'contains': assert content contained in given HTML elements.
   *
   * Assertions array has to be provided in the following format:
   *
   * [
   *   'count' => [
   *     '.ecl-page-header' => 1,
   *   ],
   *   'equals' => [
   *     '.ecl-page-header__identity' => 'Digital single market',
   *   ],
   *   'contains' => [
   *     'Digital',
   *     'single',
   *     'market',
   *   ],
   * ]
   *
   * @param string $html
   *   A render array.
   * @param array $assertions
   *   Test assertions.
   *
   * @dataProvider renderingDataProvider
   */
  public function assertRendering(string $html, array $assertions): void {
    $crawler = new Crawler($html);

    // Assert presence of given strings.
    if (isset($assertions['contains'])) {
      foreach ($assertions['contains'] as $string) {
        $this->assertContains($string, $html);
      }
    }

    // Assert occurrences of given elements.
    if (isset($assertions['count'])) {
      foreach ($assertions['count'] as $name => $expected) {
        $this->assertCount($expected, $crawler->filter($name));
      }
    }

    // Assert that a given element content equals a given string.
    if (isset($assertions['equals'])) {
      foreach ($assertions['equals'] as $name => $expected) {
        try {
          $actual = trim($crawler->filter($name)->text());
        }
        catch (\InvalidArgumentException $exception) {
          $this->fail(sprintf('Element "%s" not found (exception: "%s").', $name, $exception->getMessage()));
        }
        $this->assertEquals($expected, $actual);
      }
    }

  }

}
