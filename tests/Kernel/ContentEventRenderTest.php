<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\file\Entity\File;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that our Event content type render.
 *
 * @todo: Extend this test with more scenarios.
 */
class ContentEventRenderTest extends AbstractKernelTestBase {

  use RdfDatabaseConnectionTrait;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The node view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'field',
    'field_group',
    'options',
    'extra_field',
    'link',
    'locale',
    'content_translation',
    'language',
    'file',
    'text',
    'typed_link',
    'maxlength',
    'entity_reference',
    'entity_reference_revisions',
    'inline_entity_form',
    'composite_reference',
    'datetime',
    'datetime_range',
    'node',
    'media',
    'views',
    'entity_browser',
    'media_avportal',
    'media_avportal_mock',
    'filter',
    'oe_media',
    'oe_media_avportal',
    'oe_multilingual',
    'oe_content',
    'oe_content_timeline_field',
    'oe_content_event',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
    'oe_theme_content_event',
    'oe_theme_content_entity_contact',
    'oe_theme_content_entity_venue',
    'oe_content_social_media_links_field',
    'oe_time_caching',
    'rdf_entity',
    'rdf_skos',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpSparql();

    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', 'file_usage');
    $this->installSchema('locale', [
      'locales_location',
      'locales_target',
      'locales_source',
      'locale_file',
    ]);

    $this->installConfig([
      'address',
      'file',
      'field_group',
      'field',
      'entity_reference',
      'entity_reference_revisions',
      'inline_entity_form',
      'node',
      'media',
      'filter',
      'rdf_entity',
      'oe_media',
      'media_avportal',
      'oe_media_avportal',
      'typed_link',
      'locale',
      'language',
      'content_translation',
      'oe_multilingual',
      'composite_reference',
      'oe_time_caching',
    ]);

    // Importing of configs which related to media av_portal output.
    $this->container->get('config.installer')->installDefaultConfig('theme', 'oe_theme');

    $this->installConfig([
      'oe_content',
      'oe_content_timeline_field',
      'oe_content_social_media_links_field',
      'oe_content_event',
      'oe_content_entity',
      'oe_content_entity_contact',
      'oe_content_entity_organisation',
      'oe_content_entity_venue',
      'oe_theme_content_event',
      'oe_theme_content_entity_contact',
      'oe_theme_content_entity_venue',
    ]);

    $this->container->get('module_handler')->loadInclude('oe_multilingual', 'install');
    oe_multilingual_install();

    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('bypass node access')
      ->grantPermission('view media')
      ->save();

    $this->installEntitySchema('rdf_entity');
    $this->installEntitySchema('skos_concept');
    $this->installEntitySchema('skos_concept_scheme');

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
    $this->nodeViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('node');
  }

  /**
   * Tests that the Event featured media renders the translated media.
   */
  public function testEventFeaturedMediaTranslation(): void {
    // Make event node and image media translatable.
    $this->container->get('content_translation.manager')->setEnabled('node', 'oe_event', TRUE);
    $this->container->get('content_translation.manager')->setEnabled('media', 'image', TRUE);
    $this->container->get('router.builder')->rebuild();

    // Create image media that we will use for the English translation.
    $this->container->get('file_system')->copy(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg', 'public://example_1.jpeg');
    $en_image = File::create([
      'uri' => 'public://example_1.jpeg',
    ]);
    $en_image->save();

    // Create image media that we will use for the Bulgarian translation.
    $this->container->get('file_system')->copy(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/placeholder.png', 'public://placeholder.png');
    $bg_image = File::create([
      'uri' => 'public://placeholder.png',
    ]);
    $bg_image->save();

    // Create a media entity of image media type.
    /** @var \Drupal\media\Entity\Media $media */
    $media = $this->container->get('entity_type.manager')->getStorage('media')->create([
      'bundle' => 'image',
      'name' => 'Test image',
      'oe_media_image' => [
        'target_id' => $en_image->id(),
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
        'target_id' => $bg_image->id(),
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
      'en' => $en_image->createFileUrl(),
      'bg' => $bg_image->createFileUrl(),
    ];

    foreach ($node->getTranslationLanguages() as $node_langcode => $node_language) {
      $build = $this->nodeViewBuilder->view($node, 'default', $node_langcode);
      $html = $this->renderRoot($build);

      $crawler = new Crawler($html);

      $image = $crawler->filter('img[src="' . $file_urls[$node_langcode] . '"]');
      $this->assertCount(1, $image);
    }
  }

}
