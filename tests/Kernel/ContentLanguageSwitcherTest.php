<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test content language switcher rendering.
 */
class ContentLanguageSwitcherTest extends MultilingualAbstractKernelTest {

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
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
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
    $node->save();
    $translation = $node->addTranslation('es', ['title' => '¡Hola mundo!']);
    $translation->save();

    // Simulate a request to a node canonical route with a language prefix.
    $request = Request::create('/bg/node/1');
    // Let the Drupal router populate all the request parameters.
    $parameters = \Drupal::service('router.no_access_checks')->matchRequest($request);
    $request->attributes->add($parameters);
    // Set the prepared request as current.
    \Drupal::requestStack()->push($request);
    // Reset any discovered language. KernelTestBase creates a request to the
    // root of the website for legacy purposes, so the language is set by
    // default to the default one.
    // @see \Drupal\KernelTests\KernelTestBase::bootKernel()
    \Drupal::languageManager()->reset();

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
    $actual = $crawler->filter('.ecl-lang-select-page');
    $this->assertCount(1, $actual);

    // Make sure that unavailable language is properly rendered.
    $actual = $crawler->filter('.ecl-lang-select-page > .ecl-lang-select-page__unavailable')->text();
    $this->assertEquals('Bulgarian', $actual);

    // Make sure that selected language is properly rendered.
    $actual = $crawler->filter('.ecl-lang-select-page > .ecl-lang-select-page__list > .ecl-lang-select-page__option--is-selected')->text();
    $this->assertEquals('English', $actual);

    // Make sure that available languages are properly rendered.
    $actual = $crawler->filter('.ecl-lang-select-page  > .ecl-lang-select-page__list > .ecl-lang-select-page__option > .ecl-link')->text();
    $this->assertEquals('Spanish', $actual);
  }

}
