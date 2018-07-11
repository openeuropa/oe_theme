<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Traits;

/**
 * Helper rendering trait.
 */
trait RenderTrait {

  /**
   * Renders final HTML given a structured array tree.
   *
   * @param array $elements
   *   The structured array describing the data to be rendered.
   *
   * @return string
   *   The rendered HTML.
   *
   * @throws \Exception
   *   When called from inside another renderRoot() call.
   *
   * @see \Drupal\Core\Render\RendererInterface::render()
   */
  protected function renderRoot(array &$elements): string {
    return (string) $this->container->get('renderer')->renderRoot($elements);
  }

}
