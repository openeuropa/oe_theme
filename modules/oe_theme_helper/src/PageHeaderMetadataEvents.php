<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

/**
 * Contains all event names related to the page header metadata plugins.
 */
final class PageHeaderMetadataEvents {

  /**
   * Name of the event used for getting the node for the metadata plugins.
   *
   * @Event
   *
   * @see \Drupal\oe_theme_helper\Event\NodeMetadataEvent
   *
   * @var string
   */
  const NODE = 'page_header_metadata_events.node';

}
