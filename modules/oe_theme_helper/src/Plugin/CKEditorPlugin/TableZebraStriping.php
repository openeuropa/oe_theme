<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;

/**
 * Defines the "table_zebra_striping" with altering Table plugins.
 *
 * @CKEditorPlugin(
 *   id = "table_zebra_striping",
 *   label = @Translation("Zebra striping")
 * )
 */
class TableZebraStriping extends EclTablePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->getModulePath('oe_theme_helper') . '/js/table_zebra_striping.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'zebra_striping__checkboxLabel' => $this->t('Zebra striping'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
  }

}
