<?php

declare(strict_types = 1);

namespace Drupal\oe_link_lists;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Interface for link_display plugins.
 */
interface LinkDisplayInterface extends PluginFormInterface, ConfigurableInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label(): string;

  /**
   * Builds a render array for a list of links.
   *
   * @param \Drupal\oe_link_lists\LinkInterface[] $links
   *   The link objects.
   *
   * @return array
   *   The render array.
   */
  public function build(array $links): array;

}
