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
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 * )
 */
class FilterEclTable extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    // Ensure that we have tables in the code.
    if (stristr($text, '<table') !== FALSE) {
      $dom = Html::load($text);
      $body = $dom->getElementsByTagName('body')->item(0);
      foreach ($dom->getElementsByTagName('table') as $table) {
        $new_table = $this->getEclTable($table);
        if ($new_table) {
          $updated_node = $dom->importNode($new_table, TRUE);
          $body->replaceChild($updated_node, $table);
        }
      }
      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

  /**
   * Gets table with ECL support.
   *
   * @param \DOMElement $table
   *   Original table.
   *
   * @return \DOMElement|null
   *   Table with ECL support or NULL.
   */
  protected function getEclTable(\DOMElement $table): ?\DOMElement {
    // Create a clone to manipulate with.
    $table_node = $table->cloneNode(TRUE);

    // Skip if "tbody" tag doesn't exist.
    $tbody_node = $table_node->getElementsByTagName('tbody')->item(0);
    if (empty($tbody_node)) {
      return NULL;
    }

    // Get headers.
    $th_exist = FALSE;
    $header = $this->getTableHeader($table_node);
    if ($header) {
      $th_exist = TRUE;
    }

    // Process cells.
    $merged_cells = FALSE;
    $this->processTableCells($tbody_node, $header, $merged_cells, $th_exist);

    if (!$merged_cells && $th_exist) {
      // Return new table if there aren't merged cells and th tags exist.
      return $table_node;
    }

    return NULL;
  }

  /**
   * Gets columns titles.
   *
   * @param \DOMElement $table
   *   Table as a DOMElement object.
   *
   * @return array
   *   List of columns titles.
   */
  protected function getTableHeader(\DOMElement $table): array {
    $header = [];
    $thead = $table->getElementsByTagName('thead')->item(0);
    if (!empty($thead)) {
      // Get columns headers.
      foreach ($thead->getElementsByTagName('tr') as $tr) {
        foreach ($tr->getElementsByTagName('th') as $th) {
          if ($this->isCellMerged($th)) {
            // Merged cells aren't supported.
            return [];
          }
          $header[] = $th->nodeValue;
        }
      }
    }

    return $header;
  }

  /**
   * Processes table cells.
   *
   * @param \DOMElement $tbody
   *   Tbody node.
   * @param array $header
   *   List of columns titles.
   * @param bool $merged_cells
   *   Flag to check whether we have merged cells or not.
   * @param bool $th_exist
   *   Flag to check whether we have th tag or not.
   */
  protected function processTableCells(\DOMElement $tbody, array $header, bool &$merged_cells, bool &$th_exist): void {
    foreach ($tbody->getElementsByTagName('tr') as $tr_node) {
      $index = 0;

      foreach ($tr_node->childNodes as $node) {
        if (!$node instanceof \DOMElement) {
          continue;
        }
        // Set class to "th" and "td" tags in to support vertical headers.
        $existing_class = $node->getAttribute('class');
        $new_class = $existing_class ? "$existing_class ecl-table__cell" : 'ecl-table__cell';
        $node->setAttribute('class', $new_class);

        if ($node->tagName === 'td') {
          // Add column titles to the "td" tag to support horizontal header.
          if (!empty($header)) {
            $node->setAttribute('data-ecl-table-header', $header[$index]);
          }

          if ($this->isCellMerged($node)) {
            $merged_cells = TRUE;
          }
        }

        if ($node->tagName === 'th') {
          $th_exist = TRUE;
        }
        $index++;
      }
    }
  }

  /**
   * Checks whether cell is merged or not.
   *
   * @param \DOMElement $node
   *   Table cell.
   *
   * @return bool
   *   TRUE if cell is merged, FALSE otherwise.
   */
  protected function isCellMerged(\DOMElement $node): bool {
    // Tables with merged cells will be skipped.
    $colspan = (int) $node->getAttribute('colspan');
    if ($colspan > 1) {
      // WYSIWYG can set colspan="1" that means no merging cells.
      return TRUE;
    }

    $rowspan = (int) $node->getAttribute('rowspan');
    if ($rowspan > 1) {
      // WYSIWYG can set rowspan="1" that means no merging cells.
      return TRUE;
    }

    return FALSE;
  }

}
