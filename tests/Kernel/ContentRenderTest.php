<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that our content types render with correct markup.
 */
class ContentRenderTest extends AbstractKernelTestBase {

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
    'field',
    'link',
    'file',
    'text',
    'maxlength',
    'entity_reference',
    'datetime',
    'node',
    'media',
    'views',
    'entity_browser',
    'media_avportal',
    'media_avportal_mock',
    'filter',
    'oe_media',
    'oe_media_avportal',
    'oe_content',
    'oe_content_timeline_field',
    'oe_content_news',
    'oe_content_page',
    'oe_content_policy',
    'oe_content_publication',
    'oe_theme_content_news',
    'oe_theme_content_page',
    'oe_theme_content_policy',
    'oe_theme_content_publication',
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
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    $this->installConfig([
      'file',
      'field',
      'entity_reference',
      'node',
      'media',
      'filter',
      'rdf_entity',
      'oe_media',
      'media_avportal',
      'oe_media_avportal',
    ]);

    // Importing of configs which related to media av_portal output.
    $this->container->get('config.installer')->installDefaultConfig('theme', 'oe_theme');

    $this->installConfig([
      'oe_content',
      'oe_content_timeline_field',
      'oe_content_news',
      'oe_content_page',
      'oe_content_policy',
      'oe_content_publication',
      'oe_theme_content_news',
      'oe_theme_content_page',
      'oe_theme_content_policy',
      'oe_theme_content_publication',
    ]);

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
   * Tests that the News node type is rendered with the correct ECL markup.
   */
  public function testNews(): void {

    $media = $this->container
      ->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'av_portal_photo',
        'oe_media_avportal_photo' => 'P-038924/00-15',
        'uid' => 0,
        'status' => 1,
      ]);

    $media->save();

    $node = $this->nodeStorage->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
      'oe_news_featured_media' => [
        [
          'target_id' => (int) $media->id(),
        ],
      ],
      'oe_publication_date' => '2019-04-05',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_related_links' => [
        [
          'uri' => 'internal:/node',
          'title' => 'Node listing',
        ],
        [
          'uri' => 'https://example.com',
          'title' => 'External link',
        ],
      ],
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // Body wrapper.
    $body_wrapper = $crawler->filter('.ecl-editor');
    $this->assertCount(1, $body_wrapper);
    $this->assertContains('Body', $body_wrapper->text());

    // Featured media.
    $image_wrapper = $crawler->filter('article.ecl-u-type-paragraph picture img.ecl-u-width-100.ecl-u-height-auto');
    $this->assertCount(1, $image_wrapper);

    // Related links.
    $related_links_heading = $crawler->filter('.ecl-u-type-heading-2');
    $this->assertContains('Related links', $related_links_heading->text());
    $related_links = $crawler->filter('.ecl-list .ecl-link.ecl-link--standalone');
    $this->assertCount(2, $related_links);
    $link_one = $related_links->first();

    $this->assertContains('/node', trim($link_one->extract(['href'])[0]));
    $link_two = $related_links->first();
    $this->assertEquals('/node', trim($link_two->extract(['href'])[0]));
  }

  /**
   * Tests that the Page node type is rendered with the correct ECL markup.
   */
  public function testPage(): void {
    $node = $this->nodeStorage->create([
      'type' => 'oe_news',
      'title' => 'Test page node',
      'body' => 'Body',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_related_links' => [
        [
          'uri' => 'internal:/node',
          'title' => 'Node listing',
        ],
        [
          'uri' => 'https://example.com',
          'title' => 'External link',
        ],
      ],
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // Body wrapper.
    $body_wrapper = $crawler->filter('.ecl-editor');
    $this->assertCount(1, $body_wrapper);
    $this->assertContains('Body', $body_wrapper->text());

    // Related links.
    $related_links_heading = $crawler->filter('.ecl-u-type-heading-2');
    $this->assertContains('Related links', $related_links_heading->text());
    $related_links = $crawler->filter('.ecl-list .ecl-link.ecl-link--standalone');
    $this->assertCount(2, $related_links);
  }

  /**
   * Tests that the Policy node type is rendered with the correct ECL markup.
   */
  public function testPolicy(): void {
    $node = $this->nodeStorage->create([
      'type' => 'oe_policy',
      'title' => 'Test policy node',
      'body' => 'Body',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // Body wrapper.
    $body_wrapper = $crawler->filter('.ecl-editor');
    $this->assertCount(1, $body_wrapper);
    $this->assertContains('Body', $body_wrapper->text());
  }

  /**
   * Tests that the Publication node is rendered with the correct ECL markup.
   */
  public function testPublication(): void {
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test.pdf');
    $file->setPermanent();
    $file->save();

    $media = $this->container
      ->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'document',
        'name' => 'test document',
        'oe_media_file' => [
          'target_id' => (int) $file->id(),
        ],
        'uid' => 0,
        'status' => 1,
      ]);

    $media->save();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->nodeStorage->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_documents' => [
        [
          'target_id' => (int) $media->id(),
        ],
      ],
      'oe_summary' => 'Summary',
      'oe_publication_date' => '2019-04-05',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'default');
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // File wrapper.
    $file_wrapper = $crawler->filter('.ecl-file');
    $this->assertCount(1, $file_wrapper);

    // File row.
    $file_row = $crawler->filter('.ecl-file .ecl-file__container');
    $this->assertCount(1, $file_row);

    $file_title = $file_row->filter('.ecl-file__title');
    $this->assertContains('test document', $file_title->text());

    $file_info_language = $file_row->filter('.ecl-file__info div.ecl-file__language');
    $this->assertContains('English', $file_info_language->text());

    $file_info_properties = $file_row->filter('.ecl-file__info div.ecl-file__meta');
    $this->assertContains('KB - PDF)', $file_info_properties->text());

    $file_download_link = $file_row->filter('.ecl-file__download');
    $this->assertContains('/test.pdf', $file_download_link->attr('href'));
    $this->assertContains('Download', $file_download_link->text());
  }

}
