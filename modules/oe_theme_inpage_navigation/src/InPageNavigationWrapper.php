<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation;

use Drupal\emr\EntityMetaWrapper;

/**
 * Wrapper for the inpage navigation plugins.
 */
class InPageNavigationWrapper extends EntityMetaWrapper {

  /**
   * Checks whether the content is configured to be with inpage navigation.
   *
   * @return bool
   *   Whether it shows the children.
   */
  public function isInPageNavigation(): bool {
    return (bool) $this->entityMeta->get('oe_theme_inpage_navigation')->value;
  }

  /**
   * Sets whether the content with inpage navigation or not.
   *
   * @param bool $value
   *   Whether it is content with inpage navigation or not.
   */
  public function setInPageNavigation(bool $value): void {
    $this->entityMeta->set('oe_theme_inpage_navigation', $value);
  }

}
