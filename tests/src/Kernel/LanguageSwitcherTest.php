<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test language switcher rendering.
 *
 * @group batch2
 */
class LanguageSwitcherTest extends MultilingualAbstractKernelTestBase {

  /**
   * Test language switcher link list rendering.
   */
  public function testLanguageSwitcherLinkListRendering(): void {
    $this->assertDefaultLanguageBlock();

    // Create a few other languages than European ones.
    $language = ConfigurableLanguage::createFromLangcode('is');
    $language->save();
    $language = ConfigurableLanguage::createFromLangcode('nb');
    $language->save();
    $language = ConfigurableLanguage::createFromLangcode('zh-hans');
    $language->save();

    // Assert that Icelandic language without category won't show up by default
    // and no category titles are printed.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher overlay is present.
    $actual = $crawler->filter('div#language-list-overlay');
    $this->assertCount(1, $actual);

    // Make sure that language switcher overlay title is set.
    $actual = $crawler->filter('div#language-list-overlay .ecl-site-header__language-title')->text();
    $this->assertEquals('Select your language', trim($actual));

    // Check for EU languages category title is not present if there are no
    // non-EU languages.
    $this->assertCount(0, $crawler->filter('.ecl-site-header__language-category[data-ecl-language-list-eu] .ecl-site-header__language-category-title'));

    // Check that non-EU category is not visible by default.
    $this->assertEmpty($crawler->filter('.ecl-site-header__language-category[data-ecl-language-list-non-eu]'));

    // The Icelandic link is not rendered.
    $this->assertEmpty($crawler->filter('div#language-list-overlay a.ecl-site-header__language-link[lang=is]'));

    // Set the non eu category for the other languages.
    $language = ConfigurableLanguage::load('is');
    $language->setThirdPartySetting('oe_multilingual', 'category', 'non_eu');
    $language->save();
    $language = ConfigurableLanguage::load('nb');
    $language->setThirdPartySetting('oe_multilingual', 'category', 'non_eu');
    $language->save();
    $language = ConfigurableLanguage::load('zh-hans');
    $language->setThirdPartySetting('oe_multilingual', 'category', 'non_eu');
    $language->save();

    // Build the language block.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher overlay is present.
    $actual = $crawler->filter('div#language-list-overlay');
    $this->assertCount(1, $actual);

    // Assert there is no Icelandic language in the EU category.
    $actual = $crawler->filter("div#language-list-overlay .ecl-language-list__eu a.ecl-site-header__language-link[lang=is]");
    $this->assertCount(0, $actual);

    // Check for EU languages category.
    $actual = $crawler->filter('.ecl-site-header__language-category[data-ecl-language-list-eu] .ecl-site-header__language-category-title')->text();
    $this->assertEquals('EU official languages', trim($actual));

    // Check for non-EU languages category.
    $actual = $crawler->filter('.ecl-site-header__language-category[data-ecl-language-list-non-eu] .ecl-site-header__language-category-title')->text();
    $this->assertEquals('Other languages', trim($actual));

    // Assert there is only one link in the non-EU category.
    $actual = $crawler->filter('div#language-list-overlay .ecl-site-header__language-category[data-ecl-language-list-non-eu] a');
    $this->assertCount(3, $actual);

    // Assert the other languages links.
    $actual = $crawler->filter("div#language-list-overlay .ecl-site-header__language-category[data-ecl-language-list-non-eu] a.ecl-site-header__language-link[lang=is] span.ecl-site-header__language-link-label")->text();
    $this->assertEquals(
      'Icelandic',
      // Remove any non-printable characters from actual.
      preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $actual)
    );
    $actual = $crawler->filter("div#language-list-overlay .ecl-site-header__language-category[data-ecl-language-list-non-eu] a.ecl-site-header__language-link[lang=no] span.ecl-site-header__language-link-label")->text();
    $this->assertEquals(
      'Norwegian Bokmål',
      // Remove any non-printable characters from actual.
      preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $actual)
    );
    $actual = $crawler->filter("div#language-list-overlay .ecl-site-header__language-category[data-ecl-language-list-non-eu] a.ecl-site-header__language-link[lang=zh] span.ecl-site-header__language-link-label")->text();
    $this->assertEquals(
      'Chinese, Simplified',
      // Remove any non-printable characters from actual.
      preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $actual)
    );

    // Test backwards-compatibility by removing categories from the existing
    // languages.
    foreach (ConfigurableLanguage::loadMultiple() as $language) {
      $language->unsetThirdPartySetting('oe_multilingual', 'category');
      $language->save();
    }

    // Assert that the block renders as default without language categories.
    $this->assertDefaultLanguageBlock();
  }

  /**
   * Assert language block is rendered as default without language categories.
   */
  protected function assertDefaultLanguageBlock(): void {
    // Build the language block.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher overlay is present.
    $actual = $crawler->filter('div#language-list-overlay');
    $this->assertCount(1, $actual);

    // Make sure that language switcher overlay title is set.
    $actual = $crawler->filter('div#language-list-overlay .ecl-site-header__language-title')->text();
    $this->assertEquals('Select your language', trim($actual));

    // Check for EU languages category title is not present if there are no
    // non-EU languages.
    $this->assertCount(0, $crawler->filter('div.ecl-site-header__language-category[data-ecl-language-list-eu] .ecl-site-header__language-category-title'));

    // Check that non-EU category is not visible by default.
    $this->assertEmpty($crawler->filter('div.ecl-site-header__language-category[data-ecl-language-list-non-eu]'));

    /** @var \Drupal\Core\Language\LanguageInterface[] $languages */
    $languages = $this->container->get('language_manager')->getNativeLanguages();
    $lang_config = $this->container->get('config.factory')->get('language.negotiation');
    // Language codes mapping for Norwegian Bokmål and Chinese, Simplified.
    $map_other_language_codes = [
      'nb' => 'no',
      'zh-hans' => 'zh',
    ];

    // Make sure that language links are properly rendered.
    foreach ($this->container->get('language_manager')->getLanguages() as $language) {
      $lang_id = $language->getId();
      $lang_prefix = $map_other_language_codes[$lang_id] ?? $lang_config->get('url.prefixes.' . $lang_id);

      $actual = $crawler->filter("div#language-list-overlay a.ecl-site-header__language-link[lang={$lang_prefix}] span.ecl-site-header__language-link-label")->text();
      // Remove all non printable characters in $actual.
      $this->assertEquals(
        $languages[$lang_id]->getName(),
        preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $actual)
      );
    }
  }

  /**
   * Test language switcher rendering.
   *
   * @param string $langcode
   *   The language code.
   * @param string $langname
   *   The language name.
   *
   * @dataProvider renderingDataProvider
   */
  public function testLanguageSwitcherRendering(string $langcode, string $langname): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
    // Set the site default language.
    $this->config('system.site')->set('default_langcode', $langcode)->save();
    if ($langcode === 'pt-pt') {
      $langcode = 'pt';
    }

    // Build the language block.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher link is properly rendered.
    $actual = $crawler->filter('a[data-ecl-language-selector]')->text();
    $this->assertStringContainsString($langname, $actual);

    $actual = $crawler->filter('a[data-ecl-language-selector]')->text();
    $this->assertEquals($langname, $actual);

    // Make sure that the actual language link is set as active.
    $actual = $crawler->filter("div#language-list-overlay a.ecl-site-header__language-link.ecl-site-header__language-link--active[lang={$langcode}][hreflang={$langcode}] span.ecl-site-header__language-link-label")->text();
    $this->assertEquals($langname, trim($actual));
  }

  /**
   * Data provider for the rendering test.
   *
   * @return array
   *   An array of language codes and native language names.
   */
  public function renderingDataProvider(): array {
    return [
      ['bg', 'български'],
      ['cs', 'čeština'],
      ['da', 'dansk'],
      ['de', 'Deutsch'],
      ['et', 'eesti'],
      ['el', 'ελληνικά'],
      ['en', 'English'],
      ['es', 'español'],
      ['fr', 'français'],
      ['ga', 'Gaeilge'],
      ['hr', 'hrvatski'],
      ['it', 'italiano'],
      ['lv', 'latviešu'],
      ['lt', 'lietuvių'],
      ['hu', 'magyar'],
      ['mt', 'Malti'],
      ['nl', 'Nederlands'],
      ['pl', 'polski'],
      ['pt-pt', 'português'],
      ['ro', 'română'],
      ['sk', 'slovenčina'],
      ['sl', 'slovenščina'],
      ['fi', 'suomi'],
      ['sv', 'svenska'],
    ];
  }

  /**
   * Setup and render the language switcher block.
   *
   * @return \Symfony\Component\DomCrawler\Crawler
   *   Return the crawler.
   */
  protected function renderLanguageBlock(): Crawler {
    $block_manager = $this->container->get('plugin.manager.block');
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
