<?php

namespace Drupal\oe_theme_helper\Loader;

use Drupal\Core\Extension\ThemeHandler;
use OpenEuropa\Twig\Loader\EuropaComponentLibraryLoader;

/**
 * Class ComponentLibraryLoader.
 */
class ComponentLibraryLoader extends EuropaComponentLibraryLoader {

  /**
   * {@inheritdoc}
   */
  public function __construct($namespaces, $root, $theme, $directory, ThemeHandler $theme_handler) {
    $theme_path = $theme_handler->getTheme($theme)->getPath();
    parent::__construct($namespaces, $theme_path . DIRECTORY_SEPARATOR . $directory, $root);
  }

}
