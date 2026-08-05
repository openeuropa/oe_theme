<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\Webtools;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Tests that the Webtools smart loader follows the component library.
 *
 * @group batch2
 */
class WebtoolsSmartLoaderThemeTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_webtools',
  ];

  /**
   * Tests that the smart loader URL has the correct component library theme.
   */
  public function testSmartLoaderComponentLibrary(): void {
    $library_discovery = $this->container->get('library.discovery');

    // The default component library is EC.
    $library = $library_discovery->getLibraryByName('oe_webtools', 'drupal.webtools-smartloader');
    $this->assertCount(1, $library['js']);
    $this->assertEquals('https://webtools.europa.eu/load.js?theme=ec', $library['js'][0]['data']);

    // Change the component library to EU.
    $this->config('oe_theme.settings')->set('component_library', 'eu')->save();
    // The cached library definitions have to be cleared explicitly for the
    // change to be picked up.
    // @todo Use clear() directly when Drupal 10.6 is not supported anymore.
    if (version_compare(\Drupal::VERSION, '11.3', '<')) {
      $library_discovery->{'clearCachedDefinitions'}();
      drupal_static_reset('theme_get_setting');
    }
    else {
      $library_discovery->clear();
    }

    $library = $library_discovery->getLibraryByName('oe_webtools', 'drupal.webtools-smartloader');
    $this->assertCount(1, $library['js']);
    $this->assertEquals('https://webtools.europa.eu/load.js?theme=eu', $library['js'][0]['data']);
  }

}
