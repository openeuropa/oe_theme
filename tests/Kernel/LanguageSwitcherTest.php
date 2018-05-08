<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test language switcher rendering.
 */
class LanguageSwitcherTest extends KernelTestBase {

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
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig([
      'locale',
      'language',
      'content_translation',
      'administration_language_negotiation',
      'oe_multilingual',
    ]);

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installSchema('locale', [
      'locales_location',
      'locales_source',
      'locales_target',
    ]);

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);

    $this->container->get('module_handler')->loadInclude('oe_multilingual', 'install');
    oe_multilingual_install();
  }

  /**
   * Test language switcher rendering.
   */
  public function testLanguageSwitcherRendering(): void {

    // Setup and render language switcher block.
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [
      'id' => 'language_block:language_interface',
      'label' => 'Language switcher',
      'provider' => 'language',
      'label_display' => '0',
    ];

    /** @var \Drupal\Core\Block\BlockBase $plugin_block */
    $plugin_block = $block_manager->createInstance('language_block:language_interface', $config);
    $render = $plugin_block->build();

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that language switcher overlay is present.
    $actual = $crawler->filter('.ecl-language-list.ecl-language-list--overlay');
    $this->assertCount(1, $actual);

    // Make sure that language switcher overlay title is set.
    $actual = $crawler->filter('.ecl-dialog .ecl-dialog__title')->text();
    $this->assertEquals('Select your language', trim($actual));

    // Make sure that language switcher link is properly rendered.
    $actual = $crawler->filter('a.ecl-lang-select-sites__link > .ecl-lang-select-sites__label')->text();
    $this->assertEquals('English', $actual);

    $actual = $crawler->filter('a.ecl-lang-select-sites__link > .ecl-lang-select-sites__code > .ecl-lang-select-sites__code-text')->text();
    $this->assertEquals('en', $actual);

    // Make sure that language links are properly rendered.
    foreach (\Drupal::languageManager()->getLanguages() as $language) {
      $id = $language->getId();
      $actual = $crawler->filter(".ecl-dialog a.ecl-language-list__button[lang={$id}]")->text();
      $this->assertEquals($language->getName(), trim($actual));

      $actual = $crawler->filter(".ecl-dialog a.ecl-language-list__button[hreflang={$id}]")->text();
      $this->assertEquals($language->getName(), trim($actual));
    }

    // Make sure that English language link is set as active.
    $actual = $crawler->filter(".ecl-dialog a.ecl-language-list__button--active[lang=en]")->text();
    $this->assertEquals('English', trim($actual));
  }

}
