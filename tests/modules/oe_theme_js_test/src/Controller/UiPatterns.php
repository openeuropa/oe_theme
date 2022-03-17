<?php

declare(strict_types=1);

namespace Drupal\oe_theme_js_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for UI Patterns test routes.
 */
class UiPatterns extends ControllerBase {

  /**
   * Generates a page with test Contextual navigation rendered pattern.
   *
   * @return array
   *   The response render array.
   */
  public function contextNav(): array {
    $build = [];
    $build['context_nav_with_more_button'] = [
      '#type' => 'pattern',
      '#id' => 'context_nav',
      '#fields' => [
        'label' => $this->t('Contextual navigation with more button'),
        'items' => [
          [
            'href' => 'http://link-1.com',
            'label' => 'Item one',
          ],
          [
            'href' => 'http://link-2.com',
            'label' => 'Item two',
          ],
          [
            'href' => 'http://link-3.com',
            'label' => 'Item three',
          ],
          [
            'href' => 'http://link-4.com',
            'label' => 'Item four',
          ],
          [
            'href' => 'http://link-5.com',
            'label' => 'Item five',
          ],
        ],
        'limit' => 4,
        'more_label' => $this->t('More label'),
      ],
    ];

    $build['context_nav_without_more_button'] = [
      '#type' => 'pattern',
      '#id' => 'context_nav',
      '#fields' => [
        'label' => $this->t('Navigation title'),
        'items' => [
          [
            'href' => 'http://link-1.com',
            'label' => 'Item one',
          ],
          [
            'href' => 'http://link-2.com',
            'label' => 'Item two',
          ],
          [
            'href' => 'http://link-3.com',
            'label' => 'Item three',
          ],
        ],
        'limit' => 4,
        'more_label' => $this->t('More label'),
      ],
    ];

    return $build;
  }

}
