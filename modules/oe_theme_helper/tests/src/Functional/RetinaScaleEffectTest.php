<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Functional;

use Drupal\FunctionalTests\Image\ToolkitTestBase;

/**
 * Tests that the Retina Scale effect upscales images appropriately.
 *
 * @group image
 */
class RetinaScaleEffectTest extends ToolkitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['image', 'oe_theme_helper'];

  /**
   * The image effect manager.
   *
   * @var \Drupal\image\ImageEffectManager
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.image.effect');
  }

  /**
   * Test the retina scale effect using a big enough image.
   */
  public function testRetinaScaleEffect(): void {
    $this->assertImageEffect('retina_image_scale', [
      // Set the desired width to be smaller than the image width.
      'width' => 10,
      'height' => 10,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    // Check the parameters.
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['scale'][0][0], 10, 'Width was passed correctly');
    $this->assertEqual($calls['scale'][0][1], 10, 'Height was based off aspect ratio and passed correctly');
  }

  /**
   * Test the retina scale effect using upscaling.
   */
  public function testScaleEffectDefaultUpscaling(): void {
    $this->assertImageEffect('retina_image_scale', [
      // Set the desired width to be higher than the image width.
      'width' => $this->image->getWidth() * 4,
      'upscale' => TRUE,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    // Check the parameters.
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['scale'][0][0], $this->image->getWidth() * 4, 'Width was passed correctly');
  }

  /**
   * Test the retina scale effect using an image smalled than desired.
   */
  public function testRetinaScaleEffectForcedUpscaling(): void {
    $this->assertImageEffect('retina_image_scale', [
      // Set the desired width to be much higher than the image width.
      'width' => $this->image->getWidth() * 10,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    // Check the parameters.
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['scale'][0][0], $this->image->getWidth() * 2, 'Width is double the original size.');
  }

  /**
   * Test the retina scale effect using a multiplier of 3.
   */
  public function testTripleMultiplierRetinaScaleEffect(): void {
    $this->assertImageEffect('retina_image_scale', [
      // Set the desired width to be much higher than the image width.
      'width' => $this->image->getWidth() * 10,
      'multiplier' => 3,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    // Check the parameters.
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['scale'][0][0], $this->image->getWidth() * 3, 'Width is triple the original size.');
  }

  /**
   * Asserts the effect processing of an image effect plugin.
   *
   * @param string $effect_name
   *   The name of the image effect to test.
   * @param array $data
   *   The data to pass to the image effect.
   */
  protected function assertImageEffect($effect_name, array $data): void {
    /** @var \Drupal\image\ImageEffectInterface $effect */
    $effect = $this->manager->createInstance($effect_name, ['data' => $data]);
    $this->assertTrue($effect->applyEffect($this->image), 'Function returned the expected value.');
  }

}
