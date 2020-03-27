<?php

/**
 * @file
 * OpenEuropa theme News post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Datetime\Entity\DateFormat;

/**
 * Add a date format for the News page header metadata.
 */
function oe_theme_content_news_post_update_00001(array &$sandbox): void {
  $news_date_format = DateFormat::load('oe_theme_news_date');
  $news_date_format->set('pattern', 'j F Y');
  $news_date_format->save();
}
