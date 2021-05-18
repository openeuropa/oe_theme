<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\Tests\oe_theme\PatternAssertions\TextFeaturedMediaAssert;
use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of paragraph types with media fields.
 *
 * @group batch1
 */
class MediaParagraphsTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'media',
    'oe_media',
    'oe_media_oembed_mock',
    'oe_paragraphs_media',
    'allowed_formats',
    'oe_paragraphs_media_field_storage',
    'oe_paragraphs_iframe_media',
    'oe_paragraphs_banner',
    'views',
    'entity_browser',
    'media_avportal',
    'media_avportal_mock',
    'oe_media_avportal',
    'options',
    'oe_media_iframe',
    'file_link',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container->get('module_handler')->loadInclude('oe_paragraphs_media_field_storage', 'install');
    oe_paragraphs_media_field_storage_install();
    $this->installEntitySchema('media');
    $this->installConfig([
      'media',
      'oe_media',
      'oe_paragraphs_media',
      'media_avportal',
      'oe_media_avportal',
      'oe_paragraphs_banner',
      'oe_paragraphs_iframe_media',
      'options',
      'oe_media_iframe',
    ]);
    // Call the install hook of the Media module.
    module_load_include('install', 'media');
    media_install();
  }

  /**
   * Test text with featured media paragraph rendering.
   */
  public function testTextWithMedia(): void {
    // Create a paragraph without the media.
    $paragraph = $this->container
      ->get('entity_type.manager')
      ->getStorage('paragraph')->create([
        'type' => 'oe_text_feature_media',
        'field_oe_title' => 'Title',
        'field_oe_plain_text_long' => 'Caption',
        'field_oe_text_long' => 'Full text',
      ]);
    $paragraph->save();

    // Assert the paragraph is rendered properly without image.
    $html = $this->renderParagraph($paragraph);
    $assert = new TextFeaturedMediaAssert();
    $expected_values = [
      'title' => 'Title',
      'caption' => NULL,
      'text' => 'Full text',
      'image' => NULL,
    ];
    $assert->assertPattern($expected_values, $html);

    // Set image media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create English file.
    $en_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
    $bg_file->setPermanent();
    $bg_file->save();

    // Create a media.
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $media_storage->create([
      'bundle' => 'image',
      'name' => 'test image en',
      'oe_media_image' => [
        'target_id' => $en_file->id(),
        'alt' => 'Alt en',
      ],
    ]);
    $media->save();
    // Translate the media to Bulgarian.
    $media_bg = $media->addTranslation('bg', [
      'name' => 'test image bg',
      'oe_media_image' => [
        'target_id' => $bg_file->id(),
        'alt' => 'Alt bg',
      ],
    ]);
    $media_bg->save();

    // Add media to paragraph.
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    // Add Bulgarian translation.
    $paragraph->addTranslation('bg', ['field_oe_title' => 'Title bg'])->save();

    // Test the translated media is rendered with the translated paragraph.
    $expected_values = [
      'title' => 'Title',
      'caption' => 'Caption',
      'text' => 'Full text',
      'image' => [
        'src' => 'example_1_en.jpeg',
        'alt' => 'Alt en',
      ],
    ];
    $html = $this->renderParagraph($paragraph, 'en');
    $assert->assertPattern($expected_values, $html);

    $expected_values = [
      'title' => 'Title bg',
      'caption' => NULL,
      'text' => NULL,
      'image' => [
        'src' => 'example_1_bg.jpeg',
        'alt' => 'Alt bg',
      ],
    ];
    $html = $this->renderParagraph($paragraph, 'bg');
    $assert->assertPattern($expected_values, $html);

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $expected_values = [
      'title' => 'Title',
      'caption' => NULL,
      'text' => 'Full text',
      'image' => NULL,
    ];
    $html = $this->renderParagraph($paragraph);
    $assert->assertPattern($expected_values, $html);

    // Publish the media.
    $media->set('status', 1);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    // Remove the text and assert the element is no longer rendered.
    $paragraph->set('field_oe_text_long', '');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values = [
      'title' => 'Title',
      'caption' => 'Caption',
      'text' => NULL,
      'image' => [
        'src' => 'example_1_en.jpeg',
        'alt' => 'Alt en',
      ],
    ];
    $assert->assertPattern($expected_values, $html);

    // Remove the title and assert the element is no longer rendered.
    $paragraph->set('field_oe_title', '');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values['title'] = NULL;
    $assert->assertPattern($expected_values, $html);

    // Create a remote video and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'remote_video',
      'oe_media_oembed_video' => [
        'value' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    // Assert remote video is rendered properly.
    $video_iframe = $crawler->filter('div.ecl-media-container__media--ratio-16-9 iframe');
    $partial_iframe_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ])->toString();
    $this->assertContains($partial_iframe_url, $video_iframe->attr('src'));

    // Create an avportal video and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'av_portal_video',
      'oe_media_avportal_video' => 'I-163162',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    // Assert remote video is rendered properly.
    $html = $this->renderParagraph($paragraph);
    $expected_values = [
      'title' => NULL,
      'caption' => 'Caption',
      'text' => NULL,
      'video' => '<iframe id="videoplayerI-163162" src="//ec.europa.eu/avservices/play.cfm?ref=I-163162&amp;lg=EN&amp;sublg=none&amp;autoplay=true&amp;tin=10&amp;tout=59" frameborder="0" allowtransparency allowfullscreen webkitallowfullscreen mozallowfullscreen width="640" height="390" class="media-avportal-content"></iframe>',
      'video_ratio' => '16:9',
    ];
    $assert->assertPattern($expected_values, $html);

    // Create iframe video with aspect ration 16:9 and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'video_iframe',
      'oe_media_iframe' => '<iframe src="http://example.com"></iframe>',
      'oe_media_iframe_ratio' => '16_9',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values = [
      'title' => NULL,
      'caption' => 'Caption',
      'text' => NULL,
      'video' => '<iframe src="http://example.com"></iframe>',
      'video_ratio' => '16:9',
    ];
    $assert->assertPattern($expected_values, $html);

    // Create iframe video with aspect ration 1:1 and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'video_iframe',
      'oe_media_iframe' => '<iframe src="http://example.com"></iframe>',
      'oe_media_iframe_ratio' => '1_1',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values['video_ratio'] = '1:1';
    $assert->assertPattern($expected_values, $html);
  }

  /**
   * Test 'banner' paragraph rendering.
   */
  public function testBanner(): void {
    // Set image media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create English file.
    $en_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
    $bg_file->setPermanent();
    $bg_file->save();

    // Create a media.
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $media_storage->create([
      'bundle' => 'image',
      'name' => 'test image en',
      'oe_media_image' => [
        'target_id' => $en_file->id(),
        'alt' => 'Alt en',
      ],
    ]);
    $media->save();

    // Translate the media to Bulgarian.
    $media_bg = $media->addTranslation('bg', [
      'name' => 'test image bg',
      'oe_media_image' => [
        'target_id' => $bg_file->id(),
        'alt' => 'Alt bg',
      ],
    ]);
    $media_bg->save();

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_banner',
      'oe_paragraphs_variant' => 'oe_banner_image',
      'field_oe_title' => 'Banner',
      'field_oe_text' => 'Description',
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
        'title' => 'Example',
      ],
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
      'field_oe_banner_type' => 'hero_center',
    ]);
    $paragraph->save();
    // Add bulgarian translation.
    $paragraph->addTranslation('bg', [
      'field_oe_title' => 'Banner BG',
    ])->save();

    // Variant - image / Modifier - hero_center / Full width - No.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered'));
    $image_element = $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered div.ecl-hero-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner--full-width'));

    // Render paragraph in Bulgarian.
    $html = $this->renderParagraph($paragraph, 'bg');
    $crawler = new Crawler($html);
    $image_element = $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered div.ecl-hero-banner__image');
    // Bulgarian media should be rendered.
    $this->assertContains(
      'url(' . file_create_url($bg_file->getFileUri()) . ')',
      $image_element->attr('style')
    );

    // Variant - image / Modifier - hero_left / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->save();

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image div.ecl-hero-banner__image'));

    // Publish the media.
    $media->set('status', 1);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image'));
    $image_element = $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image div.ecl-hero-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - image / Modifier - page_center / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image.ecl-page-banner--centered'));
    $image_element = $crawler->filter('section.ecl-page-banner.ecl-page-banner--image.ecl-page-banner--centered div.ecl-page-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - image / Modifier - page_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->get('field_oe_banner_full_width')->setValue('1');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image.ecl-page-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image'));
    $image_element = $crawler->filter('section.ecl-page-banner.ecl-page-banner--image div.ecl-page-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - image-shade / Modifier - hero_center / Full width - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('oe_banner_image_shade');
    $paragraph->get('field_oe_banner_type')->setValue('hero_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade.ecl-hero-banner--centered'));
    $image_element = $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade.ecl-hero-banner--centered div.ecl-hero-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - image-shade / Modifier - hero_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade.ecl-hero-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade'));
    $image_element = $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade div.ecl-hero-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - image-shade / Modifier - page_center / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->get('field_oe_banner_full_width')->setValue('0');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade.ecl-page-banner--centered'));
    $image_element = $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade.ecl-page-banner--centered div.ecl-page-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - image-shade / Modifier - page_left / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade.ecl-page-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade'));
    $image_element = $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade div.ecl-page-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . file_create_url($en_file->getFileUri()) . ')',
      $image_element->attr('style')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - default / Modifier - hero_center / Full width - No.
    $paragraph->get('oe_paragraphs_variant')->setValue('default');
    $paragraph->get('field_oe_banner_type')->setValue('hero_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--default.ecl-hero-banner--centered'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade.ecl-hero-banner--centered div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image.ecl-page-banner--centered div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade.ecl-page-banner--centered div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - default / Modifier - hero_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->get('field_oe_banner_full_width')->setValue('1');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--default.ecl-hero-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--default'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - default / Modifier - page_center / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--default.ecl-page-banner--centered'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - default / Modifier - page_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--default.ecl-page-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--default'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - primary / Modifier - hero_center / Full width - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('oe_banner_primary');
    $paragraph->get('field_oe_banner_type')->setValue('hero_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--primary.ecl-hero-banner--centered'));

    // No image should be displayed on 'primary' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade.ecl-hero-banner--centered div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image.ecl-page-banner--centered div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade.ecl-page-banner--centered div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - primary / Modifier - hero_left / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('hero_left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--primary.ecl-hero-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--primary'));

    // No image should be displayed on 'primary' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-hero-banner__content div.ecl-hero-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-hero-banner__content p.ecl-hero-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-hero-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - primary / Modifier - page_center / Full width - Yes.
    $paragraph->get('field_oe_banner_type')->setValue('page_center');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--primary.ecl-page-banner--centered'));

    // No image should be displayed on 'primary' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade.ecl-hero-banner--centered div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image.ecl-page-banner--centered div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade.ecl-page-banner--centered div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner--full-width'));

    // Variant - primary / Modifier - page_left / Full width - No.
    $paragraph->get('field_oe_banner_type')->setValue('page_left');
    $paragraph->get('field_oe_banner_full_width')->setValue('0');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--primary.ecl-page-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-page-banner.ecl-page-banner--primary'));

    // No image should be displayed on 'primary' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image-shade div.ecl-hero-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image div.ecl-page-banner__image'));
    $this->assertCount(0, $crawler->filter('section.ecl-page-banner.ecl-page-banner--image-shade div.ecl-page-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-page-banner__content p.ecl-page-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after'));
    $this->assertContains('Example', trim($crawler->filter('div.ecl-page-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon.ecl-link--icon-after span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner--full-width'));

    // Create a media using AV Portal image and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'av_portal_photo',
      'oe_media_avportal_photo' => 'P-038924/00-15',
      'uid' => 0,
      'status' => 1,
    ]);

    $media->save();

    $paragraph = $paragraph_storage->create([
      'type' => 'oe_banner',
      'oe_paragraphs_variant' => 'oe_banner_image',
      'field_oe_text' => 'Description',
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
      'field_oe_banner_type' => 'hero_center',
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Title classes should not be rendered if the field is empty.
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner__content div.ecl-page-banner__title'));
    // Link classes should not be rendered if the field is empty.
    $this->assertCount(0, $crawler->filter('div.ecl-page-banner__content a.ecl-link'));
    $image_element = $crawler->filter('section.ecl-hero-banner.ecl-hero-banner--image.ecl-hero-banner--centered div.ecl-hero-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertContains(
      'url(' . (file_create_url('avportal://P-038924/00-15.jpg')) . ')',
      $image_element->attr('style')
    );
  }

  /**
   * Test 'Iframe' paragraph rendering.
   */
  public function testIframe(): void {
    // Set Iframe media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'iframe', TRUE);

    // Make the Iframe field translatable.
    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.iframe.oe_media_iframe');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create Iframe media with required fields and add it to the paragraph.
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $media_storage->create([
      'bundle' => 'iframe',
      'name' => 'Test Iframe',
      'oe_media_iframe' => '<iframe src="http://example.com/iframe"></iframe>',
    ]);
    $media->save();

    // Create a paragraph with required fields only.
    $paragraph = $this->container
      ->get('entity_type.manager')
      ->getStorage('paragraph')->create([
        'type' => 'oe_iframe_media',
        'field_oe_media' => [
          'target_id' => $media->id(),
        ],
      ]);
    $paragraph->save();

    // Assert the paragraph is rendered properly.
    $html = $this->renderParagraph($paragraph);

    $crawler = new Crawler($html);
    $iframe = $crawler->filter('figure.ecl-media-container.ecl-media-container--custom-ratio div.ecl-media-container__media.ecl-media-container__media--ratio-custom iframe');
    $this->assertContains('http://example.com/iframe', $iframe->attr('src'));
    $this->assertNotContains('ecl-u-type-heading-2', $html);

    // Assert "Full width" field.
    $paragraph->set('field_oe_iframe_media_full_width', TRUE)->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $iframe = $crawler->filter('figure.ecl-media-container.ecl-media-container--fullwidth.ecl-media-container--custom-ratio div.ecl-media-container__media.ecl-media-container__media--ratio-custom iframe');
    $this->assertContains('http://example.com/iframe', $iframe->attr('src'));

    // Assert ratio.
    $media->set('oe_media_iframe_ratio', '1_1')->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $iframe = $crawler->filter('figure.ecl-media-container.ecl-media-container--fullwidth div.ecl-media-container__media.ecl-media-container__media--ratio-1-1 iframe');
    $this->assertContains('http://example.com/iframe', $iframe->attr('src'));

    // Assert title and full width.
    $paragraph->set('field_oe_title', 'Iframe paragraph title');
    $paragraph->set('field_oe_iframe_media_full_width', FALSE)->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $title = $crawler->filter('h2.ecl-u-type-heading-2', $html);
    $this->assertContains('Iframe paragraph title', $title->text());
    $iframe = $crawler->filter('figure.ecl-media-container div.ecl-media-container__media.ecl-media-container__media--ratio-1-1 iframe');
    $this->assertContains('http://example.com/iframe', $iframe->attr('src'));

    // Translate the media to Bulgarian.
    $media_bg = $media->addTranslation('bg', [
      'name' => 'Test Iframe bg',
      'oe_media_iframe' => '<iframe src="http://example.com/iframe_bg"></iframe>',
    ]);
    $media_bg->save();

    // Add Bulgarian translation.
    $paragraph->addTranslation('bg', ['field_oe_title' => 'Iframe paragraph title bg'])->save();

    // Assert paragraph translation.
    $html = $this->renderParagraph($paragraph, 'bg');
    $crawler = new Crawler($html);
    $title = $crawler->filter('h2.ecl-u-type-heading-2', $html);
    $this->assertContains('Iframe paragraph title bg', $title->text());
    $iframe = $crawler->filter('figure.ecl-media-container div.ecl-media-container__media.ecl-media-container__media--ratio-1-1 iframe');
    $this->assertContains('http://example.com/iframe_bg', $iframe->attr('src'));
  }

}
