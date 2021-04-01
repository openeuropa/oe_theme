<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation;

use Drupal\emr\EntityMetaWrapper;

/**
 * Wrapper for the in-page navigation plugin.
 */
class InPageNavigationWrapper extends EntityMetaWrapper {

  /**
   * Checks whether the content is configured to be with inpage navigation.
   *
   * @return bool
   *   Whether it has in-page navigation enabled.
   */
  public function isInPageNavigationEnabled(): bool {
    return (bool) $this->entityMeta->get('oe_theme_inpage_navigation')->value;
  }

  /**
   * Sets in-page navigation setting for the node.
   *
   * @param bool $value
   *   Whether to have in-page navigation enabled.
   */
  public function setInPageNavigation(bool $value): void {
    $this->entityMeta->set('oe_theme_inpage_navigation', $value);
  }

}
