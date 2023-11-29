<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Tests\oe_theme\PatternAssertions\FileTranslationAssert;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that our media types render with correct markup.
 *
 * @group batch2
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
  protected static $modules = [
    'field',
    'file',
    'filter',
    'media',
    'oe_media',
    'oe_media_iframe',
    'file_link',
    'link',
    'options',
    'oe_webtools',
    'oe_webtools_media',
    'json_field',
    'oe_media_webtools',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    $this->installConfig([
      'file',
      'media',
      'oe_media',
      'oe_media_iframe',
      'oe_media_webtools',
      'oe_webtools_media',
      'json_field',
    ]);

    $this->mediaStorage = $this->container->get('entity_type.manager')->getStorage('media');
    $this->mediaViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('media');
  }

  /**
   * Tests that the Document media type is rendered with the correct ECL markup.
   */
  public function testDocumentMedia(): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
    // Make document media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'document', TRUE);
    // Make fields translatable.
    $field_ids = [
      'media.document.oe_media_file_type',
      'media.document.oe_media_remote_file',
      'media.document.oe_media_file',
    ];
    foreach ($field_ids as $field_id) {
      $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load($field_id);
      $field_config->set('translatable', TRUE)->save();
    }
    $this->container->get('router.builder')->rebuild();

    // Create English file.
    $english_file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.module')->getPath('oe_media') . '/tests/fixtures/sample.pdf'), 'public://test_en.pdf');
    $english_file->setPermanent();
    $english_file->save();

    // Create Spanish file.
    $spanish_file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.module')->getPath('oe_media') . '/tests/fixtures/sample.pdf'), 'public://test_es.pdf');
    $spanish_file->setPermanent();
    $spanish_file->save();

    // Create a document media. It defaults to local.
    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->mediaStorage->create([
      'bundle' => 'document',
      'name' => 'test document en',
      'oe_media_file' => [
        'target_id' => $english_file->id(),
      ],
    ]);

    $media->save();

    // View modes to test.
    $view_modes = ['default', 'oe_theme_main_content'];

    $expected_values = [
      'en' => [
        'language' => 'English',
        'meta' => '(2.96 KB - PDF)',
        'url' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://test_en.pdf'),
        'translations' => [
          [
            'title' => 'español',
            'url' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://test_es.pdf'),
            'meta' => '(2.96 KB - PDF)',
            'icon' => 'file',
          ],
          [
            'title' => 'français',
            'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'meta' => '(12.95 KB - PDF)',
            'icon' => 'file',
          ],
        ],
      ],
      'es' => [
        'language' => 'Spanish',
        'meta' => '(2.96 KB - PDF)',
        'url' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://test_es.pdf'),
        'translations' => [
          [
            'title' => 'English',
            'url' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://test_en.pdf'),
            'meta' => '(2.96 KB - PDF)',
            'icon' => 'file',
          ],
          [
            'title' => 'français',
            'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'meta' => '(12.95 KB - PDF)',
            'icon' => 'file',
          ],
        ],
      ],
      'fr' => [
        'language' => 'French',
        'meta' => '(12.95 KB - PDF)',
        'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
        'translations' => [
          [
            'title' => 'español',
            'url' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://test_es.pdf'),
            'meta' => '(2.96 KB - PDF)',
            'icon' => 'file',
          ],
          [
            'title' => 'English',
            'url' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://test_en.pdf'),
            'meta' => '(2.96 KB - PDF)',
            'icon' => 'file',
          ],
        ],
      ],
    ];

    // Assert that the media is rendered properly without translations.
    foreach ($view_modes as $view_mode) {
      $build = $this->mediaViewBuilder->view($media, $view_mode);
      $output = $this->renderRoot($build);

      $expected = [
        'button_label' => 'Download',
        'file' => [
          'title' => 'test document en',
          'url' => $expected_values['en']['url'],
          'language' => $expected_values['en']['language'],
          'meta' => $expected_values['en']['meta'],
          'icon' => 'file',
        ],
        'translations' => NULL,
      ];

      $assert = new FileTranslationAssert();
      $assert->assertPattern($expected, $output);
    }

    // Translate the media to Spanish.
    $media_spanish = $media->addTranslation('es', [
      'name' => 'test document es',
      'oe_media_file' => [
        'target_id' => $spanish_file->id(),
      ],
    ]);
    $media_spanish->save();

    // Translate the media to French, but make it remote.
    $media_french = $media->addTranslation('fr', [
      'name' => 'test document fr',
      'oe_media_file_type' => 'remote',
      'oe_media_remote_file' => [
        'uri' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
      ],
    ]);
    $media_french->save();

    // Assert that the media and its translations are rendered properly
    // in all translated languages.
    foreach ($view_modes as $view_mode) {
      foreach (['en', 'es', 'fr'] as $langcode) {
        $build = $this->mediaViewBuilder->view($media, $view_mode, $langcode);
        $output = $this->renderRoot($build);

        $expected = [
          'button_label' => 'Download',
          'file' => [
            'title' => 'test document ' . $langcode,
            'url' => $expected_values[$langcode]['url'],
            'language' => $expected_values[$langcode]['language'],
            'meta' => $expected_values[$langcode]['meta'],
            'icon' => 'file',
          ],
          'translations' => $expected_values[$langcode]['translations'],
        ];

        $assert = new FileTranslationAssert();
        $assert->assertPattern($expected, $output);
      }
    }

    // Create a document media with non-existing remote file. With this
    // File Link will not be able to determine a size or format so we are
    // testing the fallback.
    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->mediaStorage->create([
      'bundle' => 'document',
      'name' => 'test document remote HTML broken file',
      'oe_media_file_type' => 'remote',
      'oe_media_remote_file' => [
        'uri' => 'https://www.google.com/nofile',
      ],
    ]);

    $media->addTranslation('fr', [
      'name' => 'test document remote HTML broken file fr',
      'oe_media_file_type' => 'remote',
      'oe_media_remote_file' => [
        'uri' => 'https://www.google.com/nofile',
      ],
    ]);

    $media->save();

    // Assert that the media is also rendered if there is no file available.
    foreach ($view_modes as $view_mode) {
      $build = $this->mediaViewBuilder->view($media, $view_mode);
      $output = $this->renderRoot($build);

      $expected = [
        'button_label' => 'Download',
        'file' => [
          'title' => 'test document remote HTML broken file',
          'url' => 'https://www.google.com/nofile',
          'language' => 'English',
          'meta' => '(HTML)',
          'icon' => 'file',
        ],
        'translations' => [
          [
            'title' => 'français',
            'url' => 'https://www.google.com/nofile',
            'meta' => '(HTML)',
            'icon' => 'file',
          ],
        ],
      ];

      $assert = new FileTranslationAssert();
      $assert->assertPattern($expected, $output);
    }
  }

  /**
   * Tests Webtools OP Publication List media rendering.
   */
  public function testWebtoolsOpPublicationList(): void {
    // Create a OP Publication list media.
    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->mediaStorage->create([
      'bundle' => 'webtools_op_publication_list',
      'name' => 'Publication list',
      'oe_media_webtools' => '{"service":"opwidget","widgetId":"6313"}',
    ]);
    $media->save();

    // View modes to test.
    $view_modes = ['default', 'oe_theme_main_content'];
    foreach ($view_modes as $view_mode) {
      $build = $this->mediaViewBuilder->view($media, $view_mode);
      $html = $this->renderRoot($build);
      $crawler = new Crawler($html);
      // Make sure that the op publication list json is present.
      $this->assertEquals('{"service":"opwidget","widgetId":"6313"}', $crawler->filter('script')->text());
    }
  }

  /**
   * Tests that Iframe media is rendered inside media container ECL component.
   */
  public function testIframeMedia(): void {
    // Create a Iframe media without defined aspect ratio.
    $media = $this->mediaStorage->create([
      'bundle' => 'iframe',
      'name' => 'test iframe',
      'oe_media_iframe' => '<iframe src="http://example.com/iframe_media"></iframe>',
    ]);
    $media->save();

    // Assert iframe media when ratio is undefined.
    $build = $this->mediaViewBuilder->view($media, 'oe_theme_main_content');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);
    $iframe = $crawler->filter('.ecl-media-container iframe');
    $this->assertEquals('http://example.com/iframe_media', $iframe->attr('src'));

    // Assert iframe media with aspect ratio 3:2.
    $media->set('oe_media_iframe_ratio', '3_2')->save();
    $build = $this->mediaViewBuilder->view($media, 'oe_theme_main_content');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);
    $iframe = $crawler->filter('.ecl-media-container .ecl-media-container__media--ratio-3-2 iframe');
    $this->assertEquals('http://example.com/iframe_media', $iframe->attr('src'));
  }

}
