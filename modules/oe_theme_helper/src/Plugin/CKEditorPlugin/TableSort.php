<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;

/**
 * Defines the "table_sort" with altering Table plugins.
 *
 * @CKEditorPlugin(
 *   id = "table_sort",
 *   label = @Translation("Table sort")
 * )
 */
class TableSort extends EclTablePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->getModulePath('oe_theme_helper') . '/js/table_sort.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
  }

}
