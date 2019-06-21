<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\media\Entity\Media;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
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
    'datetime',
    'node',
    'media',
    'filter',
    'oe_media',
    'oe_content',
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

    $this->installConfig([
      'file',
      'node',
      'media',
      'filter',
      'rdf_entity',
      'oe_media',
      'oe_content',
      'oe_content_news',
      'oe_content_page',
      'oe_content_policy',
      'oe_content_publication',
      'oe_theme_content_news',
      'oe_theme_content_page',
      'oe_theme_content_policy',
      'oe_theme_content_publication',
    ]);

    $this->installEntitySchema('rdf_entity');
    $this->installEntitySchema('skos_concept');
    $this->installEntitySchema('skos_concept_scheme');
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
    $this->nodeViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('node');
  }

  /**
   * Tests that the News node type is rendered with the correct ECL markup.
   */
  public function testNews(): void {
    $node = $this->nodeStorage->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
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

    // Related links.
    $related_links_heading = $crawler->filter('.ecl-heading--h3');
    $this->assertContains('Related links', $related_links_heading->text());
    $related_links = $crawler->filter('.ecl-list--unstyled .ecl-list-item__link');
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
    $related_links_heading = $crawler->filter('.ecl-heading--h3');
    $this->assertContains('Related links', $related_links_heading->text());
    $related_links = $crawler->filter('.ecl-list--unstyled .ecl-list-item__link');
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
   * Tests that the Publication node type is rendered with the correct ECL markup.
   */
  public function testPublication(): void {
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test.pdf');
    $file->setPermanent();
    $file->save();

    $media = Media::create([
      'bundle' => 'document',
      'name' => 'test document',
      'oe_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);

    $media->save();

    $node = $this->nodeStorage->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_summary' => 'Summary',
      'oe_publication_date' => '2019-04-05',
      'oe_document' => $media->id(),
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // Summary wrapper.
    $summary_wrapper = $crawler->filter('.ecl-page-header .ecl-page-header__intro');
    $this->assertCount(1, $summary_wrapper);
    $this->assertContains('Summary', $summary_wrapper->text());
  }

}
