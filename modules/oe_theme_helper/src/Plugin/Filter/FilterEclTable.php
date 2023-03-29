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

    foreach ($xpath->query('//table') as $table) {
      // Put ECL related classes for table tag.
      $this->elementAddClass($table, 'ecl-table');
      // Add classes related to "Zebra striping".
      if ($table->getAttribute('data-striped') === 'true') {
        $this->elementAddClass($table, 'ecl-table--zebra');
        $table->removeAttribute('data-striped');
      }

      // Put ECL related classes for thead tag.
      foreach ($xpath->query('./thead', $table) as $thead) {
        $this->elementAddClass($thead, 'ecl-table__head');
      }

      // Put ECL related classes for tbody tag.
      foreach ($xpath->query('./tbody', $table) as $tbody) {
        $this->elementAddClass($tbody, 'ecl-table__body');
      }

      // Put ECL related classes for tr tags.
      foreach ($xpath->query('.//tr', $table) as $trow) {
        $this->elementAddClass($trow, 'ecl-table__row');
      }

      // Skip further processing of the table if table without headers.
      $ths = $xpath->query('.//th', $table);
      if ($ths->count() === 0) {
        continue;
      }
      else {
        foreach ($xpath->query('.//thead/tr[1]/th', $table) as $th) {
          $this->elementAddClass($th, 'ecl-table__header');
        }
      }

      // Do not process tables that use th cells anywhere but in the first
      // column.
      $ths_in_body = $xpath->query('.//tr[not(parent::thead)]/*[position()>1 and self::th]', $table);
      if ($ths_in_body->count() !== 0) {
        continue;
      }

      $headers = [];
      // Collect the first header row, validating that is composed only of
      // th elements.
      $has_header_row = $xpath->query('(./thead/tr[1])[count(./*[not(self::th)]) = 0]', $table);
      if ($has_header_row->count()) {
        $header_row = $has_header_row[0];
        foreach ($xpath->query('./th', $header_row) as $thead_cell) {
          $this->elementAddClass($thead_cell, 'ecl-table__header');
          // Add data attribute related to "Sort".
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
          $this->elementAddClass($cell, 'ecl-table__cell');
          if (array_key_exists($cell_index, $headers)) {
            $cell->setAttribute('data-ecl-table-header', $headers[$cell_index]);
          }
        }
      }
    }

    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

  /**
   * Adds class to element.
   *
   * @param \DOMNode $element
   *   Element.
   * @param string $class
   *   Class that should be added.
   */
  public function elementAddClass(\DOMNode $element, string $class): void {
    $classes = $element->getAttribute('class');
    $classes_array = array_filter(array_map('trim', explode(' ', $classes)));
    $classes_array[] = $class;
    $classes = implode(' ', array_unique($classes_array));
    $element->setAttribute('class', $classes);
  }

}
