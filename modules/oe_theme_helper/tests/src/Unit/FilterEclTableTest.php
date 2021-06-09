<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Unit;

use Drupal\oe_theme_helper\Plugin\Filter\FilterEclTable;
use Drupal\Tests\UnitTestCase;

/**
 * Test FilterEclTable plugin.
 *
 * @coversDefaultClass \Drupal\oe_theme_helper\Plugin\Filter\FilterEclTable
 *
 * @group batch1
 */
class FilterEclTableTest extends UnitTestCase {

  /**
   * Tests the "ECL table support" filter.
   *
   * @param string $html
   *   The html to filter.
   * @param string $expected
   *   The expected html.
   *
   * @dataProvider processDataProvider
   * @covers ::process
   */
  public function testProcess(string $html, string $expected): void {
    $filter = new FilterEclTable([], 'filter_ecl_table', ['provider' => 'test']);
    $filter->setStringTranslation($this->getStringTranslationStub());

    $processed_text = $filter->process($html, NULL)->getProcessedText();
    $this->assertEquals($expected, $processed_text);
  }

  /**
   * Data provider for testProcess().
   *
   * @return array
   *   The test data.
   */
  public function processDataProvider(): array {
    return [
      'Full table with thead and tfoot' => [
        '<p>Some text before table</p><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1">1-1</td><td>1-2</td></tr><tr><td rowspan="1">2-1</td><td>2-2</td></tr></tbody><tfoot><tr><td>Footer 1</td><td>Footer 2</td></tr></tfoot></table><p>Some text after table</p>',
        '<p>Some text before table</p><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td></tr><tr><td rowspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">2-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">2-2</td></tr></tbody><tfoot><tr><td class="ecl-table__cell" data-ecl-table-header="Column 1">Footer 1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">Footer 2</td></tr></tfoot></table><p>Some text after table</p>',
      ],
      'Table with vertical header - only class is added' => [
        '<table><tbody><tr><th>Row 1</th><td>1-1</td><td>1-2</td></tr><tr><th>Row 2</th><td>2-1</td><td>2-2</td></tr><tr><th>Row 3</th><td>3-1</td><td>3-2</td></tr></tbody></table>',
        '<table><tbody><tr><th class="ecl-table__cell">Row 1</th><td class="ecl-table__cell">1-1</td><td class="ecl-table__cell">1-2</td></tr><tr><th class="ecl-table__cell">Row 2</th><td class="ecl-table__cell">2-1</td><td class="ecl-table__cell">2-2</td></tr><tr><th class="ecl-table__cell">Row 3</th><td class="ecl-table__cell">3-1</td><td class="ecl-table__cell">3-2</td></tr></tbody></table>',
      ],
      'Table with horizontal and vertical headers' => [
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><th>Row 1</th><td>1-2</td><td>1-3</td></tr><tr><th>Row 2</th><td>2-2</td><td>2-3</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><th class="ecl-table__cell" data-ecl-table-header="Column 1">Row 1</th><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td><td class="ecl-table__cell" data-ecl-table-header="Column 3">1-3</td></tr><tr><th class="ecl-table__cell" data-ecl-table-header="Column 1">Row 2</th><td class="ecl-table__cell" data-ecl-table-header="Column 2">2-2</td><td class="ecl-table__cell" data-ecl-table-header="Column 3">2-3</td></tr></tbody></table>',
      ],
      'Table without tbody' => [
        '<table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></table>',
        '<table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tr><td class="ecl-table__cell" data-ecl-table-header="Column 1">1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td></tr><tr><td class="ecl-table__cell" data-ecl-table-header="Column 1">2-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">2-2</td></tr></table>',
      ],
      'Multiple tables' => [
        '<table><thead><tr><th>Column A1</th><th>Column A2</th></tr></thead><tbody><tr><td>A1-1</td><td>A1-2</td></tr></tbody><tfoot><tr><td>A2-1</td><td>A2-2</td></tr></tfoot></table><table><tr><td>B1-1</td><td>B1-2</td></tr><tr><td>B2-1</td><td>B2-2</td></tr></table><table><thead><tr><th>Column C1</th><th>Column C2</th><th>Column C3</th></tr></thead><tr><td>C1-1</td><td>C1-2</td><td>C1-3</td></tr><tr><td>C2-1</td><td>C2-2</td><td>C2-3</td></tr></table>',
        '<table><thead><tr><th>Column A1</th><th>Column A2</th></tr></thead><tbody><tr><td class="ecl-table__cell" data-ecl-table-header="Column A1">A1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column A2">A1-2</td></tr></tbody><tfoot><tr><td class="ecl-table__cell" data-ecl-table-header="Column A1">A2-1</td><td class="ecl-table__cell" data-ecl-table-header="Column A2">A2-2</td></tr></tfoot></table><table><tr><td>B1-1</td><td>B1-2</td></tr><tr><td>B2-1</td><td>B2-2</td></tr></table><table><thead><tr><th>Column C1</th><th>Column C2</th><th>Column C3</th></tr></thead><tr><td class="ecl-table__cell" data-ecl-table-header="Column C1">C1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column C2">C1-2</td><td class="ecl-table__cell" data-ecl-table-header="Column C3">C1-3</td></tr><tr><td class="ecl-table__cell" data-ecl-table-header="Column C1">C2-1</td><td class="ecl-table__cell" data-ecl-table-header="Column C2">C2-2</td><td class="ecl-table__cell" data-ecl-table-header="Column C3">C2-3</td></tr></table>',
      ],
      'Table without any th - not processed' => [
        '<table><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr><tr><td>3-1</td><td>3-2</td></tr></tbody></table>',
        '<table><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr><tr><td>3-1</td><td>3-2</td></tr></tbody></table>',
      ],
      'Table with cells spanning multiple rows - not processed' => [
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td rowspan="2">1-1</td><td>1-2</td><td>1-3</td></tr><tr><td>2-2</td><td>2-3</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td rowspan="2">1-1</td><td>1-2</td><td>1-3</td></tr><tr><td>2-2</td><td>2-3</td></tr></tbody></table>',
      ],
      'Table with cells spanning multiple columns - not processed' => [
        '<table><thead><tr><th colspan="2">Column 1</th><th>Column 3</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td><td>1-</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>',
        '<table><thead><tr><th colspan="2">Column 1</th><th>Column 3</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td><td>1-</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>',
      ],
      'Table with header cells spanning multiple rows - not processed' => [
        '<table><thead><tr><th rowspan="2">Column 1</th><th>Column 2</th></tr><tr><th>Column 4</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></tbody></table>',
        '<table><thead><tr><th rowspan="2">Column 1</th><th>Column 2</th></tr><tr><th>Column 4</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></tbody></table>',
      ],
      'Table with header cells spanning multiple columns - not processed' => [
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td colspan="2">1-1</td><td>1-3</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td colspan="2">1-1</td><td>1-3</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>',
      ],
      'Table with th cell in body - not processed' => [
        '<table><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tr><td>1-1</td><th>1-2</th></tr><tr><td>2-1</td><td>2-2</td></tr></table>',
        '<table><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tr><td>1-1</td><th>1-2</th></tr><tr><td>2-1</td><td>2-2</td></tr></table>',
      ],
      'Table with multiple header rows - first row is used' => [
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr><tr><th>Column 4</th><th>Column 5</th><th>Column 6</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td><td>1-3</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr><tr><th>Column 4</th><th>Column 5</th><th>Column 6</th></tr></thead><tbody><tr><td class="ecl-table__cell" data-ecl-table-header="Column 1">1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td><td class="ecl-table__cell" data-ecl-table-header="Column 3">1-3</td></tr></tbody></table>',
      ],
      'Table header with invalid header - only class is added' => [
        '<table><thead><tr><th>Column 1</th><td>Column 2</td></tr></thead><tbody><tr><td>1-1</td><td>1-2</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><td>Column 2</td></tr></thead><tbody><tr><td class="ecl-table__cell">1-1</td><td class="ecl-table__cell">1-2</td></tr></tbody></table>',
      ],
      'Table wrapped in HTML comment - not processed' => [
        '<!--<table><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></tbody></table>-->',
        '<!--<table><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></tbody></table>-->',
      ],
      'No table' => [
        '<p>Some random text with no tables around.</p>',
        '<p>Some random text with no tables around.</p>',
      ],
    ];
  }

}
