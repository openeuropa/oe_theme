<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\oe_theme\PatternAssertions\CarouselAssert;
use Drupal\Tests\oe_theme\PatternAssertions\TextFeaturedMediaAssert;
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
  protected static $modules = [
    'media',
    'oe_media',
    'oe_media_oembed_mock',
    'oe_media_webtools',
    'oe_paragraphs_media',
    'oe_webtools',
    'oe_webtools_media',
    'json_field',
    'allowed_formats',
    'oe_paragraphs_media_field_storage',
    'oe_paragraphs_iframe_media',
    'oe_paragraphs_banner',
    'oe_theme_paragraphs_banner',
    'oe_theme_paragraphs_carousel',
    'views',
    'entity_browser',
    'media_avportal',
    'media_avportal_mock',
    'oe_media_avportal',
    'options',
    'oe_media_iframe',
    'file_link',
    'oe_paragraphs_carousel',
    'composite_reference',
    'oe_paragraphs_av_media',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container->get('module_handler')->loadInclude('oe_paragraphs_media_field_storage', 'install');
    oe_paragraphs_media_field_storage_install(FALSE);
    $this->installEntitySchema('media');
    $this->installConfig([
      'json_field',
      'media',
      'oe_media',
      'oe_paragraphs_media',
      'media_avportal',
      'oe_media_avportal',
      'oe_media_webtools',
      'oe_paragraphs_banner',
      'oe_theme_paragraphs_banner',
      'oe_paragraphs_iframe_media',
      'oe_webtools_media',
      'options',
      'oe_media_iframe',
      'oe_paragraphs_carousel',
      'oe_paragraphs_av_media',
    ]);
    // Call the installation hook of the Media module.
    \Drupal::moduleHandler()->loadInclude('media', 'install');
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
        'field_oe_title' => 'Heading',
        'field_oe_feature_media_title' => 'Title',
        'field_oe_plain_text_long' => 'Caption',
        'field_oe_text_long' => 'Full text',
      ]);
    $paragraph->save();

    // Assert the paragraph is rendered properly without image.
    $html = $this->renderParagraph($paragraph);
    $assert = new TextFeaturedMediaAssert();
    $expected_values = [
      'title' => 'Heading',
      'text_title' => 'Title',
      'caption' => NULL,
      'text' => 'Full text',
      'image' => NULL,
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_simple', $html);

    // Set image media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create English file.
    $en_file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
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
    $paragraph->addTranslation('bg', [
      'field_oe_title' => 'Heading bg',
      'field_oe_feature_media_title' => 'Title bg',
      'field_oe_plain_text_long' => 'Caption bg',
      'field_oe_text_long' => 'Full text bg',
    ])->save();

    // Test the translated media is rendered with the translated paragraph.
    $expected_values = [
      'title' => 'Heading',
      'text_title' => 'Title',
      'caption' => 'Caption',
      'text' => 'Full text',
      'image' => [
        'src' => 'example_1_en.jpeg',
        'alt' => 'Alt en',
      ],
    ];
    $html = $this->renderParagraph($paragraph, 'en');
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_simple', $html);

    // Remove the alt of the image and assert empty alt is rendered.
    $media->set('oe_media_image', [
      'target_id' => $en_file->id(),
      'alt' => '',
    ]);
    $media->save();
    $expected_values['image']['alt'] = '';
    $html = $this->renderParagraph($paragraph, 'en');
    $assert->assertPattern($expected_values, $html);

    $expected_values = [
      'title' => 'Heading bg',
      'text_title' => 'Title bg',
      'caption' => 'Caption bg',
      'text' => 'Full text bg',
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
      'title' => 'Heading',
      'caption' => NULL,
      'text' => 'Full text',
      'image' => NULL,
    ];
    $html = $this->renderParagraph($paragraph);
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_simple', $html);

    // Publish the media.
    $media->set('status', 1);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    // Set back the image alt.
    $media->set('oe_media_image', [
      'target_id' => $en_file->id(),
      'alt' => 'Alt en',
    ]);
    $media->save();
    // Set the paragraph highlighted.
    $paragraph->set('field_oe_highlighted', TRUE);
    // Remove the text and assert the element is no longer rendered.
    $paragraph->set('field_oe_text_long', '');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values = [
      'title' => 'Heading',
      'caption' => 'Caption',
      'text' => NULL,
      'image' => [
        'src' => 'example_1_en.jpeg',
        'alt' => 'Alt en',
      ],
      'highlighted' => TRUE,
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_simple', $html);

    // Remove the heading and assert the element is no longer rendered.
    $paragraph->set('field_oe_title', '');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values['title'] = NULL;
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_simple', $html);

    // Remove the title and assert the element is no longer rendered.
    $paragraph->set('field_oe_feature_media_title', '');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values['text_title'] = NULL;
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_simple', $html);

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
    $media_container = $crawler->filter('div.ecl-media-container__media');
    $existing_classes = $media_container->attr('class');
    $existing_classes = explode(' ', $existing_classes);
    $this->assertNotContains('ecl-media-container__media--ratio-16-9', $existing_classes);
    $video_iframe = $media_container->filter('iframe');
    $partial_iframe_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ])->toString();
    $this->assertStringContainsString($partial_iframe_url, $video_iframe->attr('src'));
    $this->assertStringContainsString('459', $video_iframe->attr('width'));
    $this->assertStringContainsString('344', $video_iframe->attr('height'));

    // Create an avportal video and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'av_portal_video',
      'oe_media_avportal_video' => 'I-163162',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    // Assert AV Portal video is rendered properly.
    $html = $this->renderParagraph($paragraph);
    $expected_values = [
      'title' => NULL,
      'caption' => 'Caption',
      'text' => NULL,
      'video' => '<iframe id="videoplayerI-163162" src="https://ec.europa.eu/avservices/play.cfm?ref=I-163162&amp;lg=EN&amp;sublg=none&amp;autoplay=true&amp;tin=10&amp;tout=59" frameborder="0" allowtransparency allowfullscreen webkitallowfullscreen mozallowfullscreen width="576" height="324" class="media-avportal-content"></iframe>',
      'video_ratio' => '16:9',
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_simple', $html);

    // Create iframe video with aspect ratio 16:9 and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'video_iframe',
      'oe_media_iframe' => '<iframe src="http://example.com"></iframe>',
      'oe_media_iframe_ratio' => '16_9',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->set('oe_paragraphs_variant', 'left_featured');
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
    // Since link doesn't exist variant is recognized as "left_simple".
    $assert->assertVariant('left_simple', $html);

    // Create a webtools chart and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'webtools_chart',
      'name' => 'Chart',
      'oe_media_webtools' => '{"service":"charts","data":{"series":[{"name":"Y","data":[{"name":"1","y":0.5}]}]},"provider":"highcharts"}',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $assert->assertVariant('left_simple', $html);
    $crawler = new Crawler($html);
    $this->assertEquals('{"service":"charts","data":{"series":[{"name":"Y","data":[{"name":"1","y":0.5}]}]},"provider":"highcharts"}', $crawler->filter('script')->text());

    // Create iframe video with aspect ratio 1:1 and add it to the paragraph.
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
    $assert->assertVariant('left_simple', $html);

    // Assert Link field.
    $paragraph->set('field_oe_link', [
      'uri' => 'http://www.example.com/',
      'title' => 'Read more',
    ])->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values['link'] = [
      'label' => 'Read more',
      'path' => 'http://www.example.com/',
      'icon' => 'external',
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_featured', $html);

    // Assert icon of the Link field.
    $paragraph->set('field_oe_link', [
      'uri' => 'internal:/',
      'title' => 'Read more',
    ])->save();

    $html = $this->renderParagraph($paragraph);
    $expected_values['link'] = [
      'label' => 'Read more',
      'path' => '/',
      'icon' => 'corner-arrow',
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('left_featured', $html);

    // Assert default ("Text on the left, simple call to action") variant.
    $paragraph->set('oe_paragraphs_variant', 'default')->save();
    $html = $this->renderParagraph($paragraph);
    $assert->assertVariant('left_simple', $html);

    // Assert "Text on the right, simple call to action" variant.
    $paragraph->set('oe_paragraphs_variant', 'right_simple')->save();
    $html = $this->renderParagraph($paragraph);
    $assert->assertVariant('right_simple', $html);

    // Assert "Text on the left, featured call to action" variant.
    $paragraph->set('oe_paragraphs_variant', 'left_featured')->save();
    $html = $this->renderParagraph($paragraph);
    $assert->assertVariant('left_featured', $html);

    // Assert "Text on the right, featured call to action" variant.
    $paragraph->set('oe_paragraphs_variant', 'right_featured')->save();
    $html = $this->renderParagraph($paragraph);
    $assert->assertVariant('right_featured', $html);

    // Assert Link field without media.
    $paragraph->set('field_oe_media', [])->save();
    $expected_values = [
      'title' => NULL,
      'caption' => NULL,
      'text' => NULL,
      'link' => [
        'label' => 'Read more',
        'path' => '/',
        'icon' => 'corner-arrow',
      ],
    ];
    $html = $this->renderParagraph($paragraph);
    $assert->assertPattern($expected_values, $html);
    // Variant "right_featured" without media but with link will be determined
    // as "left_featured".
    $assert->assertVariant('left_featured', $html);
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
    $en_file = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
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
    // Get english file styled URI.
    $style = ImageStyle::load('oe_theme_full_width');
    $en_file_uri = $style->buildUri($en_file->getFileUri());

    // Translate the media to Bulgarian.
    $media_bg = $media->addTranslation('bg', [
      'name' => 'test image bg',
      'oe_media_image' => [
        'target_id' => $bg_file->id(),
        'alt' => 'Alt bg',
      ],
    ]);
    $media_bg->save();
    // Get bulgarian file styled URI.
    $bg_file_uri = $style->buildUri($bg_file->getFileUri());

    $paragraph_storage = $this->container->get('entity_type.manager')->getStorage('paragraph');
    $paragraph = $paragraph_storage->create([
      'type' => 'oe_banner',
      'oe_paragraphs_variant' => 'oe_banner_image',
      'field_oe_title' => 'Banner',
      'field_oe_text' => 'Description',
      'field_oe_link' => [
        'uri' => 'https://european-union.europa.eu/index_en',
        'title' => 'Example',
      ],
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
      'field_oe_banner_size' => 'large',
      'field_oe_banner_alignment' => 'centered',
    ]);
    $paragraph->save();
    // Add bulgarian translation.
    $paragraph->addTranslation('bg', [
      'field_oe_title' => 'Banner BG',
    ])->save();

    // Variant - image / Size - Large / Alignment - Centered / Full width - No.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--l.ecl-banner--centered'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--centered picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertStringContainsString('Alt en', $image_element->attr('alt'));
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertEquals('<use xlink:href="/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#corner-arrow"></use>', $crawler->filter('svg.ecl-icon.ecl-icon--xs.ecl-link__icon')->html());
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-banner--full-width'));

    // Render paragraph in Bulgarian.
    $html = $this->renderParagraph($paragraph, 'bg');
    $crawler = new Crawler($html);
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--centered picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    // Bulgarian media should be rendered.
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($bg_file_uri),
      $image_element->attr('src')
    );
    $this->assertStringContainsString('Alt bg', $image_element->attr('alt'));

    // Variant - image / Size - Large / Alignment - Left / Full width - No.
    $paragraph->get('field_oe_banner_alignment')->setValue('left');
    $paragraph->set('field_oe_link', [
      'uri' => 'https://example.com',
      'title' => 'Example',
    ]);
    $paragraph->save();

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--l picture.ecl-picture.ecl-banner__picture img.ecl-banner__image'));
    $this->assertEquals('<use xlink:href="/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external"></use>', $crawler->filter('svg.ecl-icon.ecl-icon--xs.ecl-link__icon')->html());

    // Publish the media.
    $media->set('status', 1);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--l.ecl-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-box'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-box picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-banner--full-width'));

    // Variant - image / Size - Medium / Alignment - Centered / Full width - No.
    $paragraph->get('field_oe_banner_alignment')->setValue('centered');
    $paragraph->get('field_oe_banner_size')->setValue('medium');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--m.ecl-banner--centered'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--centered picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-banner--full-width'));

    // Variant - image / Size - Medium / Alignment - Left / Full width - Yes.
    $paragraph->get('field_oe_banner_alignment')->setValue('left');
    $paragraph->get('field_oe_banner_full_width')->setValue('1');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--l.ecl-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-box.ecl-banner--m.ecl-banner--full-width'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-box picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));

    // Variant - image-shade / Size - Large / Alignment - Centered /
    // Full width - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('oe_banner_image_shade');
    $paragraph->get('field_oe_banner_alignment')->setValue('centered');
    $paragraph->get('field_oe_banner_size')->setValue('large');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--l.ecl-banner--centered.ecl-banner--full-width'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--centered picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));

    // Variant - image-shade / Size - Large / Alignment - Left /
    // Full width - Yes.
    $paragraph->get('field_oe_banner_alignment')->setValue('left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--l.ecl-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--l.ecl-banner--full-width'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-overlay picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));

    // Variant - image-shade / Size - Medium / Alignment - Centered /
    // Full width - No.
    $paragraph->get('field_oe_banner_alignment')->setValue('centered');
    $paragraph->get('field_oe_banner_size')->setValue('medium');
    $paragraph->get('field_oe_banner_full_width')->setValue('0');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--m.ecl-banner--centered'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--centered picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-banner--full-width'));

    // Variant - image-shade / Size - Medium / Alignment - Left /
    // Full width - No.
    $paragraph->get('field_oe_banner_alignment')->setValue('left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--m.ecl-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--m'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-overlay picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-banner--full-width'));

    // Variant - default / Size - Large / Alignment - Centered /
    // Full width - No.
    $paragraph->get('oe_paragraphs_variant')->setValue('default');
    $paragraph->get('field_oe_banner_alignment')->setValue('centered');
    $paragraph->get('field_oe_banner_size')->setValue('large');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--plain-background.ecl-banner--l.ecl-banner--centered'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-banner picture.ecl-picture.ecl-banner__picture img.ecl-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-banner--full-width'));

    // Variant - default / Size - Large / Alignment - Left /
    // Full width - Yes.
    $paragraph->get('field_oe_banner_alignment')->setValue('left');
    $paragraph->get('field_oe_banner_full_width')->setValue('1');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--plain-background.ecl-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--plain-background.ecl-banner--l.ecl-banner--full-width'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-banner picture.ecl-picture.ecl-banner__picture img.ecl-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));

    // Variant - default / Size - Medium / Alignment - Centered /
    // Full width - Yes.
    $paragraph->get('field_oe_banner_size')->setValue('medium');
    $paragraph->get('field_oe_banner_alignment')->setValue('centered');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--plain-background.ecl-banner--m.ecl-banner--centered.ecl-banner--full-width'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-banner picture.ecl-picture.ecl-banner__picture img.ecl-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));

    // Variant - default / Size - Medium / Alignment - Left /
    // Full width - Yes.
    $paragraph->get('field_oe_banner_alignment')->setValue('left');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--plain-background.ecl-banner--m.ecl-banner--centered'));
    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--plain-background.ecl-banner--m.ecl-banner--full-width'));

    // No image should be displayed on 'default' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-banner picture.ecl-picture.ecl-banner__picture img.ecl-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));

    // Variant - primary / Size - Large / Alignment - Centered /
    // Full width - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('oe_banner_primary');
    $paragraph->get('field_oe_banner_size')->setValue('large');
    $paragraph->get('field_oe_banner_alignment')->setValue('centered');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--plain-background.ecl-banner--l.ecl-banner--centered.ecl-banner--full-width'));

    // No image should be displayed on 'primary' variant.
    $this->assertCount(0, $crawler->filter('section.ecl-banner picture.ecl-picture.ecl-banner__picture img.ecl-banner__image'));

    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));

    // Variant - text-highlight / Size - Large / Alignment - Centered /
    // Full width - Yes.
    $paragraph->get('oe_paragraphs_variant')->setValue('oe_banner_text_highlight');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--l.ecl-banner--centered.ecl-banner--full-width'));

    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--centered picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(1, $crawler->filter('.ecl-banner--full-width'));

    // Variant - text-highlight / Size - Medium / Alignment - Left /
    // Full width - No.
    $paragraph->get('field_oe_banner_size')->setValue('medium');
    $paragraph->get('field_oe_banner_alignment')->setValue('left');
    $paragraph->get('field_oe_banner_full_width')->setValue('0');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(0, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--m.ecl-banner--centered.ecl-banner--full-width'));
    $this->assertCount(1, $crawler->filter('section.ecl-banner.ecl-banner--text-overlay.ecl-banner--m'));

    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-overlay picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($en_file_uri),
      $image_element->attr('src')
    );
    $this->assertEquals('Banner', trim($crawler->filter('div.ecl-banner__content div.ecl-banner__title')->text()));
    $this->assertEquals('Description', trim($crawler->filter('div.ecl-banner__content p.ecl-banner__description')->text()));
    $this->assertCount(1, $crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon'));
    $this->assertStringContainsString('Example', trim($crawler->filter('div.ecl-banner__content a.ecl-link.ecl-link--cta.ecl-link--icon span.ecl-link__label')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-banner--full-width'));

    // Create a media using AV Portal image and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'av_portal_photo',
      'oe_media_avportal_photo' => 'P-038924/00-15',
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();
    $av_portal_file_uri = $style->buildUri('avportal://P-038924/00-15.jpg');

    $paragraph->get('field_oe_media')->setValue([
      'target_id' => $media->id(),
    ]);
    // Empty the link and title fields.
    $paragraph->get('field_oe_title')->setValue('');
    $paragraph->get('field_oe_link')->setValue('');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Title classes should not be rendered if the field is empty.
    $this->assertCount(0, $crawler->filter('div.ecl-banner__content div.ecl-banner__title'));
    // Link classes should not be rendered if the field is empty.
    $this->assertCount(0, $crawler->filter('div.ecl-banner__content a.ecl-link'));
    $image_element = $crawler->filter('section.ecl-banner.ecl-banner--text-overlay picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      $this->container->get('file_url_generator')->generateAbsoluteString($av_portal_file_uri),
      $image_element->attr('src')
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

    // Create unpublished Iframe media with required fields to check access.
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $media_storage->create([
      'bundle' => 'iframe',
      'name' => 'Test Iframe',
      'oe_media_iframe' => '<iframe src="http://example.com/iframe"></iframe>',
      'status' => 0,
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

    // Assert unpublished media isn't shown.
    $html = $this->renderParagraph($paragraph);
    $this->assertStringNotContainsString('figure', $html);
    $this->assertStringNotContainsString('http://example.com/iframe', $html);
    $this->assertStringNotContainsString('ecl-u-type-heading-2', $html);

    // Publish media.
    $media->setPublished()->save();
    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    // Assert full width class is not present.
    $this->assertCount(0, $crawler->filter('figure.ecl-media-container__figure.ecl-media-container--full-width'));
    $iframe = $crawler->filter('figure.ecl-media-container__figure div.ecl-media-container__media iframe');
    $this->assertStringContainsString('http://example.com/iframe', $iframe->attr('src'));
    $this->assertStringNotContainsString('ecl-u-type-heading-2', $html);

    // Assert "Full width" field.
    $paragraph->set('field_oe_iframe_media_full_width', TRUE)->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $iframe = $crawler->filter('figure.ecl-media-container__figure.ecl-media-container--full-width div.ecl-media-container__media iframe');
    $this->assertStringContainsString('http://example.com/iframe', $iframe->attr('src'));

    // Assert ratio.
    $media->set('oe_media_iframe_ratio', '1_1')->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $iframe = $crawler->filter('figure.ecl-media-container__figure.ecl-media-container--full-width div.ecl-media-container__media.ecl-media-container__media--ratio-1-1 iframe');
    $this->assertStringContainsString('http://example.com/iframe', $iframe->attr('src'));

    // Assert title and full width.
    $paragraph->set('field_oe_title', 'Iframe paragraph title');
    $paragraph->set('field_oe_iframe_media_full_width', FALSE)->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $title = $crawler->filter('h2.ecl-u-type-heading-2', $html);
    $this->assertStringContainsString('Iframe paragraph title', $title->text());
    $iframe = $crawler->filter('figure.ecl-media-container__figure div.ecl-media-container__media.ecl-media-container__media--ratio-1-1 iframe');
    $this->assertStringContainsString('http://example.com/iframe', $iframe->attr('src'));

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
    $this->assertStringContainsString('Iframe paragraph title bg', $title->text());
    $iframe = $crawler->filter('figure.ecl-media-container__figure div.ecl-media-container__media.ecl-media-container__media--ratio-1-1 iframe');
    $this->assertStringContainsString('http://example.com/iframe_bg', $iframe->attr('src'));
  }

  /**
   * Test Carousel paragraph rendering.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function testCarousel(): void {
    // Set image media translatable.
    $this->container->get('content_translation.manager')
      ->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->container->get('entity_type.manager')
      ->getStorage('field_config')
      ->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create English files.
    $en_file_1 = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file_1->setPermanent();
    $en_file_1->save();
    // Get first english file styled URI.
    $style = ImageStyle::load('oe_theme_full_width');
    $en_file_1_uri = $style->buildUri($en_file_1->getFileUri());

    $en_file_2 = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_2_en.jpeg');
    $en_file_2->setPermanent();
    $en_file_2->save();
    // Get second english file styled URI.
    $en_file_2_uri = $style->buildUri($en_file_2->getFileUri());

    // Create Bulgarian files.
    $bg_file_1 = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
    $bg_file_1->setPermanent();
    $bg_file_1->save();
    // Get first bulgarian file styled URI.
    $bg_file_1_uri = $style->buildUri($bg_file_1->getFileUri());

    $bg_file_2 = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_2_bg.jpeg');
    $bg_file_2->setPermanent();
    $bg_file_2->save();
    // Get second bulgarian file styled URI.
    $bg_file_2_uri = $style->buildUri($bg_file_2->getFileUri());

    // Create a couple of media items with Bulgarian translation.
    $media_storage = $this->container->get('entity_type.manager')
      ->getStorage('media');
    $first_media = $media_storage->create([
      'bundle' => 'image',
      'name' => 'First image en',
      'oe_media_image' => [
        'target_id' => $en_file_1->id(),
        'alt' => 'First image alt en',
      ],
    ]);
    $first_media->save();
    $first_media_bg = $first_media->addTranslation('bg', [
      'name' => 'First image bg',
      'oe_media_image' => [
        'target_id' => $bg_file_1->id(),
        'alt' => 'First image alt bg',
      ],
    ]);
    $first_media_bg->save();
    $second_media = $media_storage->create([
      'bundle' => 'image',
      'name' => 'Second image en',
      'oe_media_image' => [
        'target_id' => $en_file_2->id(),
      ],
    ]);
    $second_media->save();
    $second_media_bg = $second_media->addTranslation('bg', [
      'name' => 'Second image bg',
      'oe_media_image' => [
        'target_id' => $bg_file_2->id(),
      ],
    ]);
    $second_media_bg->save();

    // Create a few Carousel items paragraphs with Bulgarian translation.
    $items = [];
    for ($i = 1; $i <= 4; $i++) {
      $paragraph = Paragraph::create([
        'type' => 'oe_carousel_item',
        'field_oe_title' => 'Item ' . $i,
        'field_oe_text' => $i % 2 === 0 ? 'Item description ' . $i : '',
        'field_oe_link' => $i % 2 === 0 ? [
          // Make sure that URI properly handled.
          'uri' => $i === 4 ? 'route:<front>' : 'http://www.example.com/',
          'title' => 'CTA ' . $i,
        ] : [],
        'field_oe_media' => $i % 2 !== 0 ? $first_media : $second_media,
      ]);
      $paragraph->save();
      $paragraph->addTranslation('bg', [
        'field_oe_title' => 'BG Item ' . $i,
        'field_oe_text' => $i % 2 === 0 ? 'BG Item description ' . $i : '',
        'field_oe_link' => $i % 2 === 0 ? [
          'uri' => 'http://www.example.com/',
          'title' => 'BG CTA ' . $i,
        ] : [],
      ])->save();
      $items[$i] = $paragraph;
    }
    // Create a Carousel paragraph with Bulgarian translation.
    $paragraph = Paragraph::create([
      'type' => 'oe_carousel',
      'oe_paragraphs_variant' => 'oe_banner_text_highlight',
      'field_oe_carousel_items' => $items,
      'field_oe_carousel_size' => 'large',
    ]);
    $paragraph->save();
    $paragraph->addTranslation('bg', $paragraph->toArray())->save();

    // Assert paragraph rendering for English version.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    // Assert the carousel size.
    $carousel_size = $crawler->filter('section.ecl-banner--l');
    $this->assertCount(4, $carousel_size);
    $assert = new CarouselAssert();
    $expected_values = [
      'items' => [
        [
          'title' => 'Item 1',
          'image' => $this->container->get('file_url_generator')->generateAbsoluteString($en_file_1_uri),
          'image_alt' => 'First image alt en',
          'variant' => 'text-overlay',
        ],
        [
          'title' => 'Item 2',
          'description' => 'Item description 2',
          'url' => 'http://www.example.com/',
          'url_text' => 'CTA 2',
          'image' => $this->container->get('file_url_generator')->generateAbsoluteString($en_file_2_uri),
          'variant' => 'text-overlay',
        ],
        [
          'title' => 'Item 3',
          'image' => $this->container->get('file_url_generator')->generateAbsoluteString($en_file_1_uri),
          'image_alt' => 'First image alt en',
          'variant' => 'text-overlay',
        ],
        [
          'title' => 'Item 4',
          'description' => 'Item description 4',
          'url' => '/',
          'url_text' => 'CTA 4',
          'image' => $this->container->get('file_url_generator')->generateAbsoluteString($en_file_2_uri),
          'variant' => 'text-overlay',
        ],
      ],
    ];
    $assert->assertPattern($expected_values, $html);

    // Assert paragraph rendering for Bulgarian version.
    $html = $this->renderParagraph($paragraph, 'bg');
    $crawler = new Crawler($html);
    // Assert the carousel size.
    $carousel_size = $crawler->filter('section.ecl-banner--l');
    $this->assertCount(4, $carousel_size);
    $expected_values['items'][0]['title'] = 'BG Item 1';
    $expected_values['items'][0]['image'] = $this->container->get('file_url_generator')->generateAbsoluteString($bg_file_1_uri);
    $expected_values['items'][0]['image_alt'] = 'First image alt bg';
    $expected_values['items'][1]['title'] = 'BG Item 2';
    $expected_values['items'][1]['description'] = 'BG Item description 2';
    $expected_values['items'][1]['url_text'] = 'BG CTA 2';
    $expected_values['items'][1]['image'] = $this->container->get('file_url_generator')->generateAbsoluteString($bg_file_2_uri);
    $expected_values['items'][2]['title'] = 'BG Item 3';
    $expected_values['items'][2]['image'] = $this->container->get('file_url_generator')->generateAbsoluteString($bg_file_1_uri);
    $expected_values['items'][2]['image_alt'] = 'First image alt bg';
    $expected_values['items'][3]['title'] = 'BG Item 4';
    $expected_values['items'][3]['description'] = 'BG Item description 4';
    $expected_values['items'][3]['url'] = 'http://www.example.com/';
    $expected_values['items'][3]['url_text'] = 'BG CTA 4';
    $expected_values['items'][3]['image'] = $this->container->get('file_url_generator')->generateAbsoluteString($bg_file_2_uri);
    $assert->assertPattern($expected_values, $html);

    // Update paragraph variant to image-overlay.
    $paragraph->set('oe_paragraphs_variant', 'oe_banner_image_shade')
      ->save();
    $html = $this->renderParagraph($paragraph, 'bg');
    foreach ($expected_values['items'] as &$item) {
      $item['variant'] = 'text-overlay';
    }
    $assert->assertPattern($expected_values, $html);

    // Update paragraph variant to default.
    $paragraph->set('oe_paragraphs_variant', 'default')
      ->save();
    $html = $this->renderParagraph($paragraph, 'bg');
    foreach ($expected_values['items'] as &$item) {
      $item['variant'] = 'plain-background';
    }
    $assert->assertPattern($expected_values, $html);
  }

  /**
   * Test Media paragraph rendering.
   */
  public function testMediaParagraph(): void {
    // Set image media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create English file.
    $en_file = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
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

    // Create a Media paragraph.
    $paragraph = $this->container
      ->get('entity_type.manager')
      ->getStorage('paragraph')->create([
        'type' => 'oe_av_media',
        'field_oe_media' => [
          'target_id' => $media->id(),
        ],
      ]);
    $paragraph->save();

    // Add Bulgarian translation.
    $paragraph->addTranslation('bg', $paragraph->toArray())->save();

    // Test the translated media is rendered with the translated paragraph.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('figure.ecl-media-container__figure img.ecl-media-container__media'));
    $image_element = $crawler->filter('figure.ecl-media-container__figure img.ecl-media-container__media');
    $this->assertStringContainsString('example_1_en.jpeg', $image_element->attr('src'));
    $this->assertStringContainsString('oe_theme_medium_2x_no_crop', $image_element->attr('src'));

    // Assert bulgarian rendering.
    $html = $this->renderParagraph($paragraph, 'bg');
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('figure.ecl-media-container__figure img.ecl-media-container__media'));
    $image_element = $crawler->filter('figure.ecl-media-container__figure img.ecl-media-container__media');
    $this->assertStringContainsString('example_1_bg.jpeg', $image_element->attr('src'));

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $this->assertCount(0, $crawler->filter('figure.ecl-media-container__figure'));

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
    $this->assertCount(1, $crawler->filter('figure.ecl-media-container__figure div.ecl-media-container__media'));
    $media_container = $crawler->filter('div.ecl-media-container__media');
    $existing_classes = $media_container->attr('class');
    $existing_classes = explode(' ', $existing_classes);
    $this->assertNotContains('ecl-media-container__media--ratio-16-9', $existing_classes);
    $video_iframe = $media_container->filter('iframe');
    $partial_iframe_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ])->toString();
    $this->assertStringContainsString($partial_iframe_url, $video_iframe->attr('src'));
    $this->assertStringContainsString('459', $video_iframe->attr('width'));
    $this->assertStringContainsString('344', $video_iframe->attr('height'));

    // Create an avportal video and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'av_portal_video',
      'oe_media_avportal_video' => 'I-163162',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    // Assert AV Portal video is rendered properly.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('figure.ecl-media-container__figure div.ecl-media-container__media'));
    $media_container = $crawler->filter('div.ecl-media-container__media');
    $this->assertEquals('<iframe id="videoplayerI-163162" src="https://ec.europa.eu/avservices/play.cfm?ref=I-163162&amp;lg=EN&amp;sublg=none&amp;autoplay=true&amp;tin=10&amp;tout=59" frameborder="0" allowtransparency allowfullscreen webkitallowfullscreen mozallowfullscreen width="576" height="324" class="media-avportal-content"></iframe>', $media_container->html());

    // Create Video iframe with ratio 16:9 and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'video_iframe',
      'oe_media_iframe' => '<iframe src="http://example.com"></iframe>',
      'oe_media_iframe_ratio' => '16_9',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('figure.ecl-media-container__figure div.ecl-media-container__media'));
    $media_container = $crawler->filter('div.ecl-media-container__media');
    $this->assertEquals('<iframe src="http://example.com"></iframe>', $media_container->html());

    // Create iframe video with aspect ratio 1:1 and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'video_iframe',
      'oe_media_iframe' => '<iframe src="http://example.com"></iframe>',
      'oe_media_iframe_ratio' => '1_1',
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $this->assertCount(1, $crawler->filter('figure.ecl-media-container__figure div.ecl-media-container__media'));
    $media_container = $crawler->filter('div.ecl-media-container__media');
    $this->assertEquals('<iframe src="http://example.com"></iframe>', $media_container->html());
    $existing_classes = $media_container->attr('class');
    $existing_classes = explode(' ', $existing_classes);
    $this->assertTrue(in_array('ecl-media-container__media--ratio-1-1', $existing_classes));
  }

}
