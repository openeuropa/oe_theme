<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that blocks are properly displayed.
 *
 * @group batch2
 */
class BlockTest extends EntityKernelTestBase {

  use RenderTrait;
  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'block',
    'block_content',
    'image',
    'breakpoint',
    'responsive_image',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('block_content');

    $this->installConfig([
      'system',
      'image',
      'responsive_image',
      'block_content',
    ]);

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);

  }

  /**
   * Test that block titles use appropriate ECL headings.
   *
   * @throws \Exception
   */
  public function testBlockTitles(): void {
    // Create a block content type.
    $block_content_type = BlockContentType::create([
      'id' => 'test_block_type',
      'label' => 'Test block type',
      'description' => "Provides a test block type",
    ]);
    $block_content_type->save();
    block_content_add_body_field($block_content_type->id());

    // And a block content entity.
    $block_content = BlockContent::create([
      'info' => 'Test block',
      'type' => 'test_block_type',
      'body' => [
        'value' => 'Test body.',
        'format' => 'plain_text',
      ],
    ]);
    $block_content->save();
    $block = $this->placeBlock('block_content:' . $block_content->uuid());
    $build = $this->container->get('entity_type.manager')->getViewBuilder('block')->view($block, 'block');

    $crawler = new Crawler($this->renderRoot($build));

    // Assert block title contains ECL classes.
    $actual = $crawler->filter('h2.ecl-u-type-heading-2');
    $this->assertCount(1, $actual);
  }

}
