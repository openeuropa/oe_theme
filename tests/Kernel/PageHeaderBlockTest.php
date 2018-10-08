<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\oe_theme\Traits\RequestTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the page header block.
 */
class PageHeaderBlockTest extends AbstractKernelTestBase {

  use RequestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
  }

  /**
   * Test the block rendering.
   */
  public function testRendering(): void {
    $node = Node::create([
      'title' => 'My first article',
      'type' => 'article',
    ]);
    $node->save();
    $this->setCurrentRequest('/node/' . $node->id());

    $config = [
      'id' => 'oe_theme_helper_page_header',
      'label' => 'Page header',
      'provider' => 'oe_theme_helper',
      'label_display' => '0',
      'context_mapping' => [
        'page_header' => '@oe_theme_helper.page_header_context:page_header',
      ],
    ];
    $build = $this->buildBlock('oe_theme_helper_page_header', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($build);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-page-header'));
    $this->assertEquals('My first article', trim($crawler->filter('.ecl-page-header__title')->text()));

    $node = Node::create([
      'title' => 'My first page',
      'type' => 'page',
    ]);
    $node->save();
    $this->setCurrentRequest('/node/' . $node->id());

    // Unset the context repository service so that the contexts are
    // recalculated.
    $this->container->set('context.repository', NULL);
    $build = $this->buildBlock('oe_theme_helper_page_header', $config);
    $html = (string) $this->container->get('renderer')->renderRoot($build);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-page-header'));
    $this->assertEquals('My first page', trim($crawler->filter('.ecl-page-header__title')->text()));
  }

}
