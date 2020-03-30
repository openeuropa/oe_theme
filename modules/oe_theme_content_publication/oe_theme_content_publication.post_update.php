<?php

/**
 * @file
 * OpenEuropa theme Publication post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Datetime\Entity\DateFormat;

/**
 * Add a date format for the Publication page header metadata.
 */
function oe_theme_content_publication_post_update_00001_add_publication_date_format(array &$sandbox): void {
  $date_format_values = [
    'langcode' => 'en',
    'status' => TRUE,
    'dependencies' => [],
    'id' => 'oe_theme_publication_date',
    'label' => 'Publication date',
    'locked' => FALSE,
    'pattern' => 'd F Y',
  ];
  $publication_date_format = DateFormat::create($date_format_values);
  $publication_date_format->save();
}

/**
 * Update a date format for the Publication page header metadata.
 */
function oe_theme_content_publication_post_update_00002(array &$sandbox): void {
  $news_date_format = DateFormat::load('oe_theme_publication_date');
  $news_date_format->set('pattern', 'j F Y');
  $news_date_format->save();
}
