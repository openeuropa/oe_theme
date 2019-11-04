<?php

declare(strict_types = 1);

namespace Drupal\oe_link_lists\Plugin\LinkDisplay;

use Drupal\oe_link_lists\LinkDisplayPluginBase;

/**
 * Teaser display of link list links.
 *
 * Renders a simple list of links.
 *
 * @LinkDisplay(
 *   id = "teaser",
 *   label = @Translation("Teaser"),
 *   description = @Translation("Link list with title and teaser."),
 * )
 */
class Teaser extends LinkDisplayPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build(array $links): array {
    $items = [];
    foreach ($links as $link) {
      $item = [
        'url' => $link->getUrl(),
        'title' => $link->getTitle(),
        'detail' => $link->getTeaser(),
      ];

      $items[] = [
        '#type' => 'pattern',
        '#id' => 'list_item',
        '#variant' => 'navigation',
        '#fields' => $item,
      ];
    }

    // We need to wrap the return value in a child array because #type does not
    // seem to work in this context. See same/similar issue with lazy_builders:
    // https://www.drupal.org/project/drupal/issues/2609250.
    return [
      [
        '#type' => 'pattern',
        '#id' => 'list_item_block_one_column',
        '#fields' => [
          'items' => $items,
          'title' => $this->configuration['title'],
        ],
      ],
    ];
  }

}
