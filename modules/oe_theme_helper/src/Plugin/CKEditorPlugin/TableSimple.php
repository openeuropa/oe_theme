<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;

/**
 * Defines the "table_simple" with altering Table plugins.
 *
 * @deprecated This is a CKEditor 4 plugin.
 * CKEditor 4 is not supported in Drupal 11.
 * This plugin will be removed in version 6.x+ of the theme.
 *
 * @CKEditorPlugin(
 *   id = "table_simple",
 *   label = @Translation("Simple table")
 * )
 */
class TableSimple extends EclTablePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->getModulePath('oe_theme_helper') . '/js/table_simple.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'simple__checkboxLabel' => $this->t('Simple table'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
  }

}
