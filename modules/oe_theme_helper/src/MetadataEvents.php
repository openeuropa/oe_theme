<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

/**
 * Contains all events related to the metadata plugin.
 */
final class MetadataEvents {

  /**
   * Name of the event fired during collecting info for metadata building.
   *
   * @Event
   *
   * @var string
   */
  const COLLECT_ENTITY = 'oe_theme_helper.metadata_collecting_entity_data';

}
