<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Loader;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use OpenEuropa\Twig\Loader\EuropaComponentLibraryLoader;

/**
 * Load ECL components Twig templates.
 */
class ComponentLibraryLoader extends EuropaComponentLibraryLoader {

  use MessengerTrait;

  /**
   * Theme path, if any.
   *
   * @var string
   */
  protected $themePath;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct($namespaces, $root, $theme, $directory, ThemeHandlerInterface $theme_handler, LoggerChannelFactoryInterface $logger_factory) {
    // Make sure the theme exists before getting its path.
    // This is necessary when the "oe_theme_helper" module is enabled before
    // the theme is or the theme is disabled and the "oe_theme_helper" is not.
    $path = '';
    if ($theme_handler->themeExists($theme)) {
      $this->themePath = $theme_handler->getTheme($theme)->getPath();
      $path = $this->themePath . DIRECTORY_SEPARATOR . $directory;
    }

    $this->logger = $logger_factory->get('ecl');
    parent::__construct($namespaces, $path, $root, 'twig-component-', 'ecl-');
  }

}
