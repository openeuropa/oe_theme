<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_theme_helper\Traits\ToolkitTestTrait;

/**
 * Tests that the Retina Scale effect upscales images appropriately.
 *
 * @group image
 *
 * @group batch2
 */
class RetinaScaleEffectTest extends KernelTestBase {

  use ToolkitTestTrait;

  /**
   * Testing image.
   *
   * @var \Drupal\Core\Image\ImageInterface
   */
  protected $image;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'image',
    'image_test',
    'oe_theme_helper',
    'system',
  ];

  /**
   * The image effect manager.
   *
   * @var \Drupal\image\ImageEffectManager
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.image.effect');
    $this->image = $this->getImage();
    $this->imageTestReset();
  }

  /**
   * Test the retina scale effect using a big enough image.
   */
  public function testRetinaScaleEffect(): void {
    $this->assertImageEffect(['scale'], 'retina_image_scale', [
      // Set the desired width to be smaller than the image width.
      'width' => 10,
      'height' => 10,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    $calls = $this->imageTestGetAllCalls();
    $this->assertEquals(10, $calls['scale'][0][0]);
    $this->assertEquals(10, $calls['scale'][0][1]);
  }

  /**
   * Test the retina scale effect using upscaling.
   */
  public function testScaleEffectDefaultUpscaling(): void {
    $this->assertImageEffect(['scale'], 'retina_image_scale', [
      // Set the desired width to be higher than the image width.
      'width' => $this->image->getWidth() * 4,
      'upscale' => TRUE,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    $calls = $this->imageTestGetAllCalls();
    $this->assertEquals($this->image->getWidth() * 4, $calls['scale'][0][0]);
  }

  /**
   * Test the retina scale effect using an image smaller than desired.
   */
  public function testRetinaScaleEffectForcedUpscaling(): void {
    $this->assertImageEffect(['scale'], 'retina_image_scale', [
      // Set the desired width to be higher than the image width.
      'width' => $this->image->getWidth() * 10,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    $calls = $this->imageTestGetAllCalls();
    $this->assertEquals($this->image->getWidth() * 2, $calls['scale'][0][0]);
  }

  /**
   * Test the retina scale effect using a multiplier of 3.
   */
  public function testTripleMultiplierRetinaScaleEffect(): void {
    $this->assertImageEffect(['scale'], 'retina_image_scale', [
      // Set the desired width to be higher than the image width.
      'width' => $this->image->getWidth() * 10,
      'multiplier' => 3,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    $calls = $this->imageTestGetAllCalls();
    $this->assertEquals($this->image->getWidth() * 3, $calls['scale'][0][0]);
  }

  /**
   * Asserts the effect processing of an image effect plugin.
   *
   * @param array $operations
   *   Array with the operations to be done.
   * @param string $effect_name
   *   The name of the image effect to test.
   * @param array $data
   *   The data to pass to the image effect.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function assertImageEffect(array $operations, string $effect_name, array $data): void {
    /** @var \Drupal\image\ImageEffectInterface $effect */
    $effect = $this->manager->createInstance($effect_name, ['data' => $data]);
    $this->assertTrue($effect->applyEffect($this->image));
  }

}
