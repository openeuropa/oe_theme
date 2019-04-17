<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Test language switcher rendering.
 */
class LanguageSwitcherTest extends MultilingualAbstractKernelTestBase {

  /**
   * Test language switcher link list rendering.
   */
  public function testLanguageSwitcherLinkListRendering(): void {
    // Build the language block.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher overlay is present.
    $actual = $crawler->filter('.ecl-language-list.ecl-language-list--overlay');
    $this->assertCount(1, $actual);

    /** @var \Drupal\Core\Language\LanguageInterface[] $languages */
    $languages = \Drupal::service('language_manager')->getNativeLanguages();

    // Make sure that language links are properly rendered.
    foreach (\Drupal::languageManager()->getLanguages() as $language) {
      $id = $language->getId();
      $name = $languages[$id]->getName();

      // Get the language prefix.
      $lang_prefix = \Drupal::configFactory()
        ->getEditable('language.negotiation')
        ->get('url.prefixes.' . $id);

      $actual = $crawler->filter(".ecl-dialog a.ecl-language-list__button[lang={$lang_prefix}]")->text();
      $this->assertEquals($name, trim($actual));

      $actual = $crawler->filter(".ecl-dialog a.ecl-language-list__button[hreflang={$id}]")->text();
      $this->assertEquals($name, trim($actual));
    }
  }

  /**
   * Test language switcher rendering.
   *
   * @dataProvider renderingDataProvider
   */
  public function testLanguageSwitcherRendering($langcode): void {
    // Set the site default language.
    $this->config('system.site')->set('default_langcode', $langcode)->save();

    // Build the language block.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher overlay title is set.
    $actual = $crawler->filter('.ecl-dialog .ecl-dialog__title')->text();
    $this->assertEquals('Select your language', trim($actual));

    /** @var \Drupal\Core\Language\LanguageInterface[] $languages */
    $languages = \Drupal::service('language_manager')->getNativeLanguages();

    // Make sure that language switcher link is properly rendered.
    $actual = $crawler->filter('a.ecl-lang-select-sites__link > .ecl-lang-select-sites__label')->text();
    $this->assertEquals($languages[$langcode]->getName(), $actual);

    // Get the language prefix.
    $lang_prefix = \Drupal::configFactory()
      ->getEditable('language.negotiation')
      ->get('url.prefixes.' . $langcode);
    $actual = $crawler->filter('a.ecl-lang-select-sites__link > .ecl-lang-select-sites__code > .ecl-lang-select-sites__code-text')->text();
    $this->assertEquals($lang_prefix, $actual);

    // Make sure that English language link is set as active.
    $actual = $crawler->filter(".ecl-dialog a.ecl-language-list__button--active[lang={$lang_prefix}]")->text();
    $this->assertEquals($languages[$langcode]->getName(), trim($actual));
  }

  /**
   * Data provider for the rendering test.
   *
   * @return array
   *   An array of langcodes.
   */
  public function renderingDataProvider(): array {
    return [
      ['bg'],
      ['cs'],
      ['da'],
      ['de'],
      ['et'],
      ['el'],
      ['en'],
      ['es'],
      ['fr'],
      ['ga'],
      ['hr'],
      ['it'],
      ['lv'],
      ['lt'],
      ['hu'],
      ['mt'],
      ['nl'],
      ['pl'],
      ['pt-pt'],
      ['ro'],
      ['sk'],
      ['sl'],
      ['fi'],
      ['sv'],
    ];
  }

  /**
   * Setup and render the language switcher block.
   *
   * @return \Symfony\Component\DomCrawler\Crawler
   *   Return the clawler.
   */
  protected function renderLanguageBlock(): Crawler {
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

    return new Crawler($html);
  }

}
