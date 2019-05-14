<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

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
    'text',
    'maxlength',
    'datetime',
    'node',
    'media',
    'filter',
    'oe_content',
    'oe_content_news',
    'oe_content_page',
    'oe_theme_content_news',
    'oe_theme_content_page',
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
      'node',
      'media',
      'filter',
      'rdf_entity',
      'oe_content',
      'oe_content_news',
      'oe_content_page',
      'oe_theme_content_news',
      'oe_theme_content_page',
    ]);

    $this->installEntitySchema('rdf_entity');
    $this->installEntitySchema('skos_concept');
    $this->installEntitySchema('skos_concept_scheme');
    $this->installEntitySchema('media');

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
      'oe_summary' => 'Summery',
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

}
