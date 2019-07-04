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
   * Modules to enable.
   *
   * @var array
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
   * Test the retina_image_scale_effect() function.
   */
  public function testRetinaScaleEffect() {
    $this->assertImageEffect('retina_image_scale', [
      // Set the desired width to be much higher than the image width.
      'width' => $this->image->getWidth() * 10,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    // Check the parameters.
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['scale'][0][0], $this->image->getWidth() * 2, 'Width is double the original size.');
    $this->assertEqual($calls['scale'][0][1], $this->image->getHeight() * 2, 'Height is double the original size.');
  }

  /**
   * Test the image_scale_effect() function using a multiplier of 3.
   */
  public function testTripleMultiplierRetinaScaleEffect() {
    $this->assertImageEffect('retina_image_scale', [
      // Set the desired width to be much higher than the image width.
      'width' => $this->image->getWidth() * 10,
      // Set a multiplier of three for the test.
      'multiplier' => 3,
    ]);
    $this->assertToolkitOperationsCalled(['scale']);

    // Check the parameters.
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['scale'][0][0], $this->image->getWidth() * 3, 'Width is triple the original size.');
    $this->assertEqual($calls['scale'][0][1], $this->image->getHeight() * 3, 'Height is triple the original size.');
  }

  /**
   * Asserts the effect processing of an image effect plugin.
   *
   * @param string $effect_name
   *   The name of the image effect to test.
   * @param array $data
   *   The data to pass to the image effect.
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  protected function assertImageEffect($effect_name, array $data) {
    /** @var \Drupal\image\ImageEffectInterface $effect */
    $effect = $this->manager->createInstance($effect_name, ['data' => $data]);
    return $this->assertTrue($effect->applyEffect($this->image), 'Function returned the expected value.');
  }

}
