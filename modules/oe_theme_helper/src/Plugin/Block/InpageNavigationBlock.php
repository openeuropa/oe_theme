<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an In-page navigation block.
 *
 * @Block(
 *   id = "oe_theme_helper_inpage_navigation",
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
      '#theme' => 'oe_theme_helper_inpage_navigation_block',
      '#title' => $this->t('Page contents'),
      '#attached' => [
        'library' => [
          'oe_theme/inpage_navigation',
        ],
      ],
    ];
    return $build;
  }

}
