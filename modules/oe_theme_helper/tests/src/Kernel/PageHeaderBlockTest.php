<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_theme\Traits\RequestTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the page header block.
 */
class PageHeaderBlockTest extends AbstractKernelTestBase {

  use RequestTrait;

  /**
   * The state key value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_test',
    'page_header_metadata_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('entity_test');

    $this->state = $this->container->get('state');
  }

  /**
   * Test the block rendering.
   */
  public function testRendering(): void {
    $entity = EntityTest::create([
      'name' => 'Example page',
    ]);
    $entity->save();
    $this->setCurrentRequest('/entity_test/' . $entity->id());

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
    $this->assertEquals('Example page', trim($crawler->filter('.ecl-page-header__title')->text()));
    $this->assertCount(0, $crawler->filter('.ecl-page-header__identity'));
    $this->assertCount(0, $crawler->filter('.ecl-page-header__intro'));
    $this->assertCount(0, $crawler->filter('.ecl-meta--header .ecl-meta__item'));

    $entity = EntityTest::create([
      'name' => 'Another example page',
    ]);
    $entity->save();

    $paths = [
      '/entity_test/' . $entity->id(),
      '/entity_test_rev/' . $entity->id() . '/revision/' . $entity->getRevisionId() . '/view',
    ];

    foreach ($paths as $path) {
      $this->setCurrentRequest($path);

      // Unset the context repository service so that the contexts are
      // recalculated.
      $this->container->set('context.repository', NULL);
      $build = $this->buildBlock('oe_theme_helper_page_header', $config);
      $html = (string) $this->container->get('renderer')->renderRoot($build);
      $crawler = new Crawler($html);

      $this->assertCount(1, $crawler->filter('.ecl-page-header'));
      $this->assertEquals('Another example page', trim($crawler->filter('.ecl-page-header__title')->text()));
      $this->assertCount(0, $crawler->filter('.ecl-page-header__identity'));
      $this->assertCount(0, $crawler->filter('.ecl-page-header__intro'));
      $this->assertCount(0, $crawler->filter('.ecl-meta--header .ecl-meta__item'));
    }

    // Enable the test plugin and add some metadata.
    $this->state->set('page_header_test_plugin_applies', TRUE);
    $this->state->set('page_header_test_plugin_metadata', [
      'identity' => 'Custom site identity',
      'title' => 'Custom page title.',
      'introduction' => 'Custom page introduction.',
      'metas' => [
        'Custom meta 1',
        'Custom meta 2',
        'Custom meta 3',
      ],
    ]);

    // Regenerate the block.
    $this->container->set('context.repository', NULL);
    $build = $this->buildBlock('oe_theme_helper_page_header', $config);
    $html = (string) $this->container->get('renderer')->renderRoot($build);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-page-header'));
    $this->assertEquals('Custom site identity', trim($crawler->filter('.ecl-page-header__identity')->text()));
    $this->assertEquals('Custom page title.', trim($crawler->filter('.ecl-page-header__title')->text()));
    $this->assertEquals('Custom page introduction.', trim($crawler->filter('.ecl-page-header__intro')->text()));

    $metas = array_column(iterator_to_array($crawler->filter('.ecl-meta--header .ecl-meta__item')), 'nodeValue');
    $this->assertEquals([
      'Custom meta 1',
      'Custom meta 2',
      'Custom meta 3',
    ], $metas);
  }

}
