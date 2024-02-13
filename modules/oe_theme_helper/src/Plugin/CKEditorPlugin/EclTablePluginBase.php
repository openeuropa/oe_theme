<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\editor\Entity\Editor;

/**
 * Base class for ECL table related plugins.
 */
abstract class EclTablePluginBase extends CKEditorPluginBase implements CKEditorPluginContextualInterface {

  /**
   * {@inheritdoc}
   */
  public function isEnabled(Editor $editor) {
    if (!$editor->hasAssociatedFilterFormat()) {
      return FALSE;
    }

    // ECL table related plugin can be enabled only when the ECL table filter is
    // enabled and the table button is present in the WYSIWYG toolbar.
    $enabled = FALSE;
    $format = $editor->getFilterFormat();
    if ($format->filters('filter_ecl_table')->status) {
      $settings = $editor->getSettings();
      foreach ($settings['toolbar']['rows'] as $row) {
        foreach ($row as $group) {
          foreach ($group['items'] as $button) {
            if ($button === 'Table') {
              $enabled = TRUE;
            }
          }
        }
      }
    }

    return $enabled;
  }

}
