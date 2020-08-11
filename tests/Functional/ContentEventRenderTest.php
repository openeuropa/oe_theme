<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that our Event content type render.
 *
 * @todo: Extend this test with ecl/markup rendering tests.
 */
class ContentEventRenderTest extends BrowserTestBase {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_event',
    'content_translation',
    'oe_multilingual',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    $this->container->get('plugin.manager.ui_patterns')->clearCachedDefinitions();

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
  }

  /**
   * Tests that the Event featured media renders the translated media.
   */
  public function testEventFeaturedMediaTranslation(): void {
    // Make event node and image media translatable.
    $this->container->get('content_translation.manager')->setEnabled('node', 'oe_event', TRUE);
    $this->container->get('content_translation.manager')->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create image media that we will use for the English translation.
    $en_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
    $bg_file->setPermanent();
    $bg_file->save();

    // Create a media entity of image media type.
    /** @var \Drupal\media\Entity\Media $media */
    $media = $this->container->get('entity_type.manager')->getStorage('media')->create([
      'bundle' => 'image',
      'name' => 'Test image',
      'oe_media_image' => [
        'target_id' => $en_file->id(),
        'alt' => 'default en alt',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    // Add a Bulgarian translation.
    $media->addTranslation('bg', [
      'name' => 'Test image bg',
      'oe_media_image' => [
        'target_id' => $bg_file->id(),
        'alt' => 'default bg alt',
      ],
    ]);
    $media->save();

    // Create an Event node in English translation.
    $node = $this->nodeStorage->create([
      'type' => 'oe_event',
      'title' => 'Test event node',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
      'oe_event_dates' => [
        'value' => '2030-05-10T12:00:00',
        'end_value' => '2030-05-15T12:00:00',
      ],
      'oe_event_featured_media' => [
        'target_id' => (int) $media->id(),
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $node->addTranslation('bg', ['title' => 'Test event bg']);
    $node->save();

    $file_urls = [
      'en' => $en_file->createFileUrl(),
      'bg' => $bg_file->createFileUrl(),
    ];

    foreach ($node->getTranslationLanguages() as $node_langcode => $node_language) {
      $node = $this->container->get('entity.repository')->getTranslationFromContext($node, $node_langcode);
      $this->drupalGet($node->toUrl());
      $this->assertSession()->elementExists('css', 'figure[class="ecl-media-container"] img[src*="' . $file_urls[$node_langcode] . '"][alt="default ' . $node_langcode . ' alt"]');
    }
  }

}
