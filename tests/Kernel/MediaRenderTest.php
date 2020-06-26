<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Language\LanguageManager;
use Drupal\oe_theme_helper\EuropeanUnionLanguages;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that our media types render with correct markup.
 */
class MediaRenderTest extends MultilingualAbstractKernelTestBase {

  /**
   * The media storage.
   *
   * @var \Drupal\media\MediaStorage
   */
  protected $mediaStorage;

  /**
   * The media view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $mediaViewBuilder;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'file',
    'media',
    'oe_media',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    $this->installConfig([
      'file',
      'media',
      'oe_media',
    ]);

    $this->mediaStorage = $this->container->get('entity_type.manager')->getStorage('media');
    $this->mediaViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('media');
  }

  /**
   * Tests that the Document media type is rendered with the correct ECL markup.
   */
  public function testDocumentMedia(): void {
    // Make image media translatable.
    $setting = $this->container->get('entity_type.manager')->getStorage('language_content_settings')->create([
      'langcode' => 'en',
      'statur' => TRUE,
      'id' => 'media.document',
      'target_entity_type_id' => 'media',
      'target_bundle' => 'document',
      'default_langcode' => 'site_default',
      'language_alterable' => 'true',
    ]);
    $setting->setThirdPartySetting('content_translation', 'enabled', TRUE);
    $setting->save();

    $english_file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test_en.pdf');
    $english_file->setPermanent();
    $english_file->save();

    $spanish_file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test_es.pdf');
    $spanish_file->setPermanent();
    $spanish_file->save();

    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->mediaStorage->create([
      'bundle' => 'document',
      'name' => 'test document EN',
      'oe_media_file' => [
        'target_id' => $english_file->id(),
      ],
    ]);

    $media->save();

    $media_spanish = $media->addTranslation('es', [
      'name' => 'test document ES',
      'oe_media_file' => [
        'target_id' => $spanish_file->id(),
      ],
    ]);
    $media_spanish->save();

    $translation_languages = $media->getTranslationLanguages();
    foreach (['default', 'oe_theme_main_content'] as $view_mode) {
      foreach ($translation_languages as $document_langcode => $document_language) {
        $build = $this->mediaViewBuilder->view($media, $view_mode, $document_langcode);
        $crawler = new Crawler($this->renderRoot($build));

        // Check the rendering of the current language document.
        // File wrapper.
        $file_wrapper = $crawler->filter('.ecl-file');
        $this->assertCount(1, $file_wrapper);

        // File row.
        $file_row = $crawler->filter('.ecl-file .ecl-file__container');
        $this->assertCount(1, $file_row);

        $file_title = $file_row->filter('.ecl-file__title');
        $this->assertContains('test document', $file_title->text());

        $file_info_language = $file_row->filter('.ecl-file__info div.ecl-file__language');
        $this->assertContains($document_language->getName(), $file_info_language->text());

        $file_info_properties = $file_row->filter('.ecl-file__info div.ecl-file__meta');
        $this->assertContains('KB - PDF)', $file_info_properties->text());

        $file_download_link = $file_row->filter('.ecl-file__download');
        $this->assertContains('/test_' . $document_langcode . '.pdf', $file_download_link->attr('href'));
        $this->assertContains('Download', $file_download_link->text());

        // Check the rendering of the available translations.
        // File translation list.
        $translation_list = $crawler->filter('.ecl-file__translation-list');
        $this->assertCount(1, $translation_list);

        // Check the translation list description.
        $translation_list_description = $translation_list->filter('.ecl-file__translation-item.ecl-file__translation-description');
        $this->assertContains('Looking for another language which is not on the list? Find out why.', $translation_list_description->text());

        // Get the available translation language.
        $translation_language = array_filter($translation_languages, function ($langcode) use ($document_langcode) {
          return $document_langcode != $langcode;
        }, ARRAY_FILTER_USE_KEY);
        /** @var \Drupal\Core\Language\Language $translation_language */
        $translation_language = reset($translation_language);

        $language_names = $predefined = EuropeanUnionLanguages::getLanguageList() + LanguageManager::getStandardLanguageList();
        $translation_file_info_language = $translation_list->filter('.ecl-file__translation-item .ecl-file__translation-info div.ecl-file__translation-title');
        $this->assertContains($language_names[$translation_language->getId()][1], $translation_file_info_language->text());

        $translation_file_info_properties = $translation_list->filter('.ecl-file__translation-item .ecl-file__translation-info div.ecl-file__translation-meta');
        $this->assertContains('KB - PDF)', $translation_file_info_properties->text());

        $translation_file_download_link = $translation_list->filter('.ecl-file__translation-download');
        $this->assertContains('/test_' . $translation_language->getId() . '.pdf', $translation_file_download_link->attr('href'));
        $this->assertContains('Download', $file_download_link->text());
      }
    }
  }

}
