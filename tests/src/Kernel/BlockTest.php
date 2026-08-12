<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that blocks are properly displayed.
 *
 * @group batch4
 */
class BlockTest extends EntityKernelTestBase {

  use RenderTrait;
  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'block',
    'block_content',
    'field',
    'filter',
    'text',
    'image',
    'breakpoint',
    'responsive_image',
    'oe_theme_helper',
    'oe_time_caching',
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
    $field_storage = FieldStorageConfig::loadByName('block_content', 'body');
    if (!$field_storage) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => 'body',
        'entity_type' => 'block_content',
        'type' => 'text_with_summary',
      ]);
      $field_storage->save();
    }
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'test_block_type',
      'label' => 'Body',
      'settings' => ['display_summary' => FALSE],
    ])->save();

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
