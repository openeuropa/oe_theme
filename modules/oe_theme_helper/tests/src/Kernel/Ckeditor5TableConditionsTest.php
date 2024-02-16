<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme_helper\Kernel;

use Drupal\editor\EditorInterface;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Tests the conditions for the CKEditor 5 table plugins.
 *
 * @group batch2
 */
class Ckeditor5TableConditionsTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ckeditor5',
    'editor',
    'filter',
  ];

  /**
   * Tests the conditions for the CKEditor 5 table plugins.
   */
  public function testConditions(): void {
    /** @var \Drupal\ckeditor5\Plugin\CKEditor5PluginManagerInterface $manager */
    $manager = $this->container->get('plugin.manager.ckeditor5.plugin');
    $table_plugins = [
      'oe_theme_helper_table_simple',
      'oe_theme_helper_table_sort',
      'oe_theme_helper_table_zebra_striping',
    ];
    // The table plugins are active only if the ECL table filter and the
    // core table plugins are enabled.
    // The core table plugin is enabled by placing the related table button
    // in the toolbar.
    $this->assertSame([], array_intersect($table_plugins, array_keys($manager->getEnabledDefinitions($this->getTestEditor(FALSE, FALSE)))));
    $this->assertSame([], array_intersect($table_plugins, array_keys($manager->getEnabledDefinitions($this->getTestEditor(FALSE, TRUE)))));
    $this->assertSame([], array_intersect($table_plugins, array_keys($manager->getEnabledDefinitions($this->getTestEditor(TRUE, FALSE)))));
    $this->assertSame($table_plugins, array_intersect($table_plugins, array_keys($manager->getEnabledDefinitions($this->getTestEditor(TRUE, TRUE)))));
  }

  /**
   * Creates a pair filter/editor for the test.
   *
   * @param bool $filter_status
   *   The status of the ECL table filter plugin.
   * @param bool $table_toolbar_enabled
   *   Whether the table toolbar button should be placed in the editor or not.
   *
   * @return \Drupal\editor\EditorInterface
   *   The test editor instance.
   */
  protected function getTestEditor(bool $filter_status, bool $table_toolbar_enabled): EditorInterface {
    $format = FilterFormat::create([
      'format' => $this->randomMachineName(),
      'name' => $this->randomMachineName(),
      'weight' => 1,
      'roles' => [],
      'filters' => [
        'filter_ecl_table' => [
          'status' => $filter_status,
        ],
      ],
    ]);
    $format->save();

    $editor = Editor::create([
      'editor' => 'ckeditor5',
      'format' => $format->id(),
      'settings' => [
        'toolbar' => [
          'items' => $table_toolbar_enabled ? ['insertTable'] : [],
        ],
      ],
      'image_upload' => [
        'status' => FALSE,
      ],
    ]);
    $editor->save();

    return $editor;
  }

}
