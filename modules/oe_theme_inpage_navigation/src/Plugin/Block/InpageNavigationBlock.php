<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an In-page navigation block.
 *
 * @Block(
 *   id = "oe_theme_inpage_navigation_menu",
 *   admin_label = @Translation("Inpage navigation block"),
 *   category = @Translation("OpenEuropa"),
 * )
 */
class InpageNavigationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#theme' => 'oe_theme_inpage_navigation_block',
      '#title' => $this->t('Page contents'),
    ];
    return $build;
  }

}
