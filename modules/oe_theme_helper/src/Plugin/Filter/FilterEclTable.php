<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to support ECL tables.
 *
 * @Filter(
 *   id = "filter_ecl_table",
 *   title = @Translation("ECL table support"),
 *   description = @Translation("Add classes and attributes to the table to align it on mobile devices."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 * )
 */
class FilterEclTable extends FilterBase {

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    // Ensure that we have tables in the code.
    if (stristr($text, '<table') === FALSE) {
      return $result;
    }

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    foreach ($xpath->query('//table[.//th]') as $table) {
      // Skip the table if any cell spans over multiple columns or rows.
      $span_cells = $xpath->query('.//*[self::th or self::td][(@colspan and @colspan > 1) or (@rowspan and @rowspan > 1)]', $table);
      if ($span_cells->count() !== 0) {
        continue;
      }

      // Do not process tables that use th cells anywhere but in the first
      // column.
      $ths_in_body = $xpath->query('.//tr[not(parent::thead)]/*[position()>1 and self::th]', $table);
      if ($ths_in_body->count() !== 0) {
        continue;
      }

      // Put ECL related classes for table tag.
      $table_classes = ltrim($table->getAttribute('class') . ' ecl-table');
      // Add related to "Zebra striping" classes.
      if ($table->getAttribute('data-striped') === 'true') {
        $table_classes .= ' ecl-table--zebra';
        $table->removeAttribute('data-striped');
      }
      $table->setAttribute('class', $table_classes);

      // Put ECL related classes for thead tag.
      foreach ($xpath->query('./thead', $table) as $thead) {
        $thead->setAttribute('class', ltrim($thead->getAttribute('class') . ' ecl-table__head'));
      }

      // Put ECL related classes for tbody tag.
      foreach ($xpath->query('./tbody', $table) as $tbody) {
        $tbody->setAttribute('class', ltrim($tbody->getAttribute('class') . ' ecl-table__body'));
      }

      // Put ECL related classes for tr tags.
      foreach ($xpath->query('.//tr', $table) as $trow) {
        $trow->setAttribute('class', ltrim($trow->getAttribute('class') . ' ecl-table__row'));
      }

      $headers = [];
      // Collect the first header row, validating that is composed only of
      // th elements.
      $has_header_row = $xpath->query('(./thead/tr[1])[count(./*[not(self::th)]) = 0]', $table);
      if ($has_header_row->count()) {
        $header_row = $has_header_row[0];
        foreach ($xpath->query('./th', $header_row) as $thead_cell) {
          $thead_cell->setAttribute('class', ltrim($thead_cell->getAttribute('class') . ' ecl-table__header'));
          // Add related to "Sort" data attribute.
          if ($thead_cell->getAttribute('data-sortable') === 'true') {
            $thead_cell->setAttribute('data-ecl-table-sort-toggle', '');
            $thead_cell->removeAttribute('data-sortable');

            // Add additional attributes to enable sorting for table.
            $table->setAttribute('data-ecl-table', '');
            $table->setAttribute('data-ecl-auto-init', 'Table');
          }
          $headers[] = $thead_cell->nodeValue;
        }
      }

      // Loop through all the table rows, aside from header ones.
      foreach ($xpath->query('.//tr[not(parent::thead)]', $table) as $row) {
        // Fetch all the cells inside the row.
        foreach ($xpath->query('./*[self::th or self::td]', $row) as $cell_index => $cell) {
          $cell->setAttribute('class', ltrim($cell->getAttribute('class') . ' ecl-table__cell'));
          if (array_key_exists($cell_index, $headers)) {
            $cell->setAttribute('data-ecl-table-header', $headers[$cell_index]);
          }
        }
      }
    }

    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

}
