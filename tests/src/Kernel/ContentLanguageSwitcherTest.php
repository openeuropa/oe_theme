<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\oe_theme\Traits\RequestTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test content language switcher rendering.
 *
 * @group batch2
 */
class ContentLanguageSwitcherTest extends MultilingualAbstractKernelTestBase {

  use RequestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
  }

  /**
   * Test language switcher rendering.
   */
  public function testLanguageSwitcherRendering(): void {
    $node = Node::create([
      'title' => 'Hello, world!',
      'type' => 'oe_demo_translatable_page',
    ]);
    /** @var \Drupal\Core\Entity\EntityInterface $translation */
    $node->addTranslation('es', ['title' => '¡Hola mundo!'])->save();

    // Simulate a request to the canonical route of the node with Bulgarian
    // language prefix.
    $this->setCurrentRequest('/bg/node/' . $node->id());

    // Setup and render language switcher block.
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [
      'id' => 'oe_multilingual_content_language_switcher',
      'label' => 'Content language switcher',
      'provider' => 'oe_multilingual',
      'label_display' => '0',
    ];

    /** @var \Drupal\Core\Block\BlockBase $plugin_block */
    $plugin_block = $block_manager->createInstance('oe_multilingual_content_language_switcher', $config);
    $render = $plugin_block->build();

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that content language switcher block is present.
    $actual = $crawler->filter('div.ecl-lang-select-page div.ecl-container');
    $this->assertCount(1, $actual);

    // Warning message doesn't contain the unavailable language, the translation
    // will have it.
    $this->assertUnavailableLanguage($crawler, 'English is available via machine translation – please use the link below.');

    // Make sure that selected language is properly rendered.
    $this->assertSelectedLanguage($crawler, 'English');

    // Make sure that available languages are properly rendered.
    $this->assertTranslationLinks($crawler, ['español']);

    // Remove the spanish translation.
    $node->removeTranslation('es');
    $node->save();

    // Re-render the block assuming a request to the Spanish version of the
    // node.
    $this->setCurrentRequest('/es/node/' . $node->id());
    $render = $plugin_block->build();

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Verify that the requested language is set as unavailable.
    $this->assertUnavailableLanguage($crawler, 'English is available via machine translation – please use the link below.');

    // Verify that the content has been rendered in the fallback language.
    $this->assertSelectedLanguage($crawler, 'English');

    // Make sure that no language links are rendered.
    $this->assertTranslationLinks($crawler, []);
  }

  /**
   * Asserts that a language is marked as unavailable.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The content language block crawler.
   * @param string $expected
   *   The label of the language.
   */
  protected function assertUnavailableLanguage(Crawler $crawler, string $expected): void {
    $actual = $crawler->filter('div.ecl-lang-select-page div.ecl-container div.ecl-message--warning')->text();
    $this->assertStringContainsString($expected, trim($actual));
  }

  /**
   * Asserts that a language is marked as the current rendered.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The content language block crawler.
   * @param string $expected
   *   The label of the language.
   */
  protected function assertSelectedLanguage(Crawler $crawler, string $expected): void {
    $actual = $crawler->filter('div.ecl-lang-select-page div.ecl-container div.ecl-expandable__content li.ecl-unordered-list__item a.ecl-u-bg-blue-50')->text();
    $this->assertEquals($expected, trim($actual));
  }

  /**
   * Asserts the rendered translation links in the content language switcher.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The content language block crawler.
   * @param array $expected
   *   The labels of the translations that should be rendered as links.
   */
  protected function assertTranslationLinks(Crawler $crawler, array $expected): void {
    $elements = $crawler->filter('div.ecl-lang-select-page div.ecl-container div.ecl-expandable__content li.ecl-unordered-list__item a:not(.ecl-u-bg-blue-50)');
    $this->assertSameSize($expected, $elements);

    $actual = array_column(iterator_to_array($elements), 'nodeValue');
    $this->assertEquals($expected, $actual);
  }

}
