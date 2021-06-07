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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->markTestSkipped('Skip this test temporarily, as part of ECL v3 upgrade.');
  }

  /**
   * Test language switcher link list rendering.
   */
  public function testLanguageSwitcherLinkListRendering(): void {
    $this->assertDefaultLanguageBlock();

    // Create the Icelandic language.
    $language = ConfigurableLanguage::createFromLangcode('is');
    $language->save();

    // Assert that Icelandic language without category won't show up by default
    // and no category titles are printed.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher overlay is present.
    $actual = $crawler->filter('.ecl-language-list--overlay');
    $this->assertCount(1, $actual);

    // Make sure that language switcher overlay title is set.
    $actual = $crawler->filter('.ecl-language-list--overlay .ecl-language-list__title')->text();
    $this->assertEquals('Select your language', trim($actual));

    // Check for EU languages category title is not present if there are no
    // non-EU languages.
    $this->assertEmpty($crawler->filter('.ecl-language-list__eu .ecl-language-list__category')->text());

    // Check that non-EU category is not visible by default.
    $this->assertEmpty($crawler->filter('.ecl-language-list__non-eu'));

    // The Icelandic link is not rendered.
    $this->assertEmpty($crawler->filter('.ecl-language-list--overlay a.ecl-language-list__link[lang=is]'));

    // Set the category for Icelandic language.
    $language = ConfigurableLanguage::load('is');
    $language->setThirdPartySetting('oe_multilingual', 'category', 'non_eu');
    $language->save();

    // Build the language block.
    $crawler = $this->renderLanguageBlock();

    // Make sure that language switcher overlay is present.
    $actual = $crawler->filter('.ecl-language-list--overlay');
    $this->assertCount(1, $actual);

    // Assert there is no Icelandic language in the EU category.
    $actual = $crawler->filter(".ecl-language-list--overlay .ecl-language-list__eu a.ecl-language-list__link[lang=is]");
    $this->assertCount(0, $actual);

    // Check for EU languages category.
    $actual = $crawler->filter('.ecl-language-list__eu .ecl-language-list__category')->text();
    $this->assertEquals('EU official languages', trim($actual));

    // Check for non-EU languages category.
    $actual = $crawler->filter('.ecl-language-list__non-eu .ecl-language-list__category')->text();
    $this->assertEquals('Non-EU languages', trim($actual));

    // Assert there is only one link in the non-EU category.
    $actual = $crawler->filter('.ecl-language-list--overlay .ecl-language-list__non-eu a');
    $this->assertCount(1, $actual);

    // Assert the Icelandic language link.
    $actual = $crawler->filter(".ecl-language-list--overlay .ecl-language-list__non-eu a.ecl-language-list__link[lang=is]")->text();
    $this->assertEquals(
      'Icelandic',
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
    $actual = $crawler->filter('.ecl-language-list--overlay');
    $this->assertCount(1, $actual);

    // Make sure that language switcher overlay title is set.
    $actual = $crawler->filter('.ecl-language-list--overlay .ecl-language-list__title')->text();
    $this->assertEquals('Select your language', trim($actual));

    // Check for EU languages category title is not present if there are no
    // non-EU languages.
    $this->assertEmpty($crawler->filter('.ecl-language-list__eu .ecl-language-list__category')->text());

    // Check that non-EU category is not visible by default.
    $this->assertEmpty($crawler->filter('.ecl-language-list__non-eu'));

    /** @var \Drupal\Core\Language\LanguageInterface[] $languages */
    $languages = $this->container->get('language_manager')->getNativeLanguages();
    $lang_config = $this->container->get('config.factory')->get('language.negotiation');

    // Make sure that language links are properly rendered.
    foreach ($this->container->get('language_manager')->getLanguages() as $language) {
      $lang_id = $language->getId();
      $lang_prefix = $lang_config->get('url.prefixes.' . $lang_id);

      $actual = $crawler->filter(".ecl-language-list--overlay a.ecl-language-list__link[lang={$lang_prefix}]")->text();
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
   * @param string $lang_prefix
   *   The language prefix.
   *
   * @dataProvider renderingDataProvider
   */
  public function testLanguageSwitcherRendering(string $langcode, string $lang_prefix): void {
    // Set the site default language.
    $this->config('system.site')->set('default_langcode', $langcode)->save();

    // Build the language block.
    $crawler = $this->renderLanguageBlock();

    /** @var \Drupal\Core\Language\LanguageInterface[] $languages */
    $languages = $this->container->get('language_manager')->getNativeLanguages();
    $language_name = $languages[$langcode]->getName();

    // Make sure that language switcher link is properly rendered.
    $actual = $crawler->filter('a[data-ecl-language-selector]')->text();
    $this->assertContains($language_name, $actual);

    $actual = $crawler->filter('a[data-ecl-language-selector] .ecl-site-header-core__language-code')->text();
    $this->assertEquals($lang_prefix, $actual);

    // Make sure that the actual language link is set as active.
    $actual = $crawler->filter(".ecl-language-list--overlay .ecl-language-list__item--is-active a.ecl-language-list__link[lang={$lang_prefix}]")->text();
    $this->assertEquals($language_name, trim($actual));
  }

  /**
   * Data provider for the rendering test.
   *
   * @return array
   *   An array of langcodes and prefixes.
   */
  public function renderingDataProvider(): array {
    return [
      ['bg', 'bg'],
      ['cs', 'cs'],
      ['da', 'da'],
      ['de', 'de'],
      ['et', 'et'],
      ['el', 'el'],
      ['en', 'en'],
      ['es', 'es'],
      ['fr', 'fr'],
      ['ga', 'ga'],
      ['hr', 'hr'],
      ['it', 'it'],
      ['lv', 'lv'],
      ['lt', 'lt'],
      ['hu', 'hu'],
      ['mt', 'mt'],
      ['nl', 'nl'],
      ['pl', 'pl'],
      ['pt-pt', 'pt'],
      ['ro', 'ro'],
      ['sk', 'sk'],
      ['sl', 'sl'],
      ['fi', 'fi'],
      ['sv', 'sv'],
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
