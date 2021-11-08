<?php

/**
 * @file
 * Backward compatibility layer to support Toolkit kernel tests in Drupal 8.9.
 *
 * As soon as Drupal 8.9 compatibility is dropped, this file can be removed and
 * core's ToolkitTestTrait trait should be used instead.
 *
 * @see https://www.drupal.org/node/3035573
 * @see \Drupal\Tests\Traits\Core\Image\ToolkitTestTrait
 */

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Traits;

use Drupal\Core\Image\ImageInterface;
use Drupal\Tests\TestFileCreationTrait;

if (version_compare(\Drupal::VERSION, '9.0.0', '>=')) {
  /**
   * Wraps the Drupal 9 ToolkitTestTrait trait.
   */
  trait ToolkitTestTrait {

    use \Drupal\Tests\Traits\Core\Image\ToolkitTestTrait;

  }
}
else {
  /**
   * Provides Drupal 8.9 Toolkit kernel tests backward compatibility layer.
   *
   * This trait is a copy of \Drupal\Tests\Traits\Core\Image\ToolkitTestTrait
   * from Drupal >=9.1.
   */
  trait ToolkitTestTrait {

    use TestFileCreationTrait {
      getTestFiles as drupalGetTestFiles;
    }

    /**
     * Resets/initializes the history of calls to the test toolkit functions.
     */
    protected function imageTestReset(): void {
      \Drupal::state()->delete('image_test.results');
    }

    /**
     * Asserts that all of the specified image operations are called once.
     *
     * @param string[] $expected
     *   Array containing the operation names, e.g. load, save, crop, etc.
     */
    public function assertToolkitOperationsCalled(array $expected): void {
      // If one of the image operations is expected, 'apply' should be expected
      // as well.
      $operations = [
        'resize',
        'rotate',
        'crop',
        'desaturate',
        'create_new',
        'scale',
        'scale_and_crop',
        'my_operation',
        'convert',
      ];
      if (count(array_intersect($expected, $operations)) > 0 && !in_array('apply', $expected)) {
        $expected[] = 'apply';
      }

      // Determine which operations were called.
      $actual = array_keys(array_filter($this->imageTestGetAllCalls()));

      // Determine if there were any expected that were not called.
      $uncalled = array_diff($expected, $actual);
      $this->assertEmpty($uncalled);

      // Determine if there were any unexpected calls. If all unexpected calls
      // are operations and apply was expected, we do not count it as an error.
      $unexpected = array_diff($actual, $expected);
      $assert = !(count($unexpected) && (!in_array('apply', $expected) || count(array_intersect($unexpected, $operations)) !== count($unexpected)));
      $this->assertTrue($assert);
    }

    /**
     * Gets an array of calls to the 'test' toolkit.
     *
     * @return array
     *   An array keyed by operation name ('parseFile', 'save', 'settings',
     *   'resize', 'rotate', 'crop', 'desaturate') with values being arrays of
     *   parameters passed to each call.
     */
    protected function imageTestGetAllCalls(): array {
      return \Drupal::state()->get('image_test.results', []);
    }

    /**
     * Sets up an image with the custom toolkit.
     *
     * @return \Drupal\Core\Image\ImageInterface
     *   The image object.
     */
    protected function getImage(): ImageInterface {
      $image_factory = \Drupal::service('image.factory');
      $file = current($this->drupalGetTestFiles('image'));
      $image = $image_factory->get($file->uri, 'test');
      $this->assertTrue($image->isValid());
      return $image;
    }

    /**
     * Asserts the effect processing of an image effect plugin.
     *
     * @param string[] $expected_operations
     *   String array containing the operation names, e.g. load, save, etc.
     * @param string $effect_name
     *   The name of the image effect to test.
     * @param array $data
     *   The data to be passed to the image effect.
     */
    protected function assertImageEffect(array $expected_operations, string $effect_name, array $data): void {
      $effect = $this->imageEffectPluginManager->createInstance($effect_name, ['data' => $data]);
      $image = $this->getImage();
      $this->imageTestReset();
      $this->assertTrue($effect->applyEffect($image));
      $this->assertToolkitOperationsCalled($expected_operations);
    }

  }
}
