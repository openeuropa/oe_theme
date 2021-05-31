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
      'Simple table with header - class and data-ecl-table-header are added' => [
        '<p>Some text before table</p><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1">1-1</td><td>1-2</td></tr><tr><td rowspan="1">2-1</td><td>2-2</td></tr></tbody></table><p>Some text after table</p>',
        '<p>Some text before table</p><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td></tr><tr><td rowspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">2-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">2-2</td></tr></tbody></table><p>Some text after table</p>',
      ],
      'Table with footer - class and data-ecl-table-header are added' => [
        '<div><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1">1-1</td><td>1-2</td></tr><tr><td rowspan="1">2-1</td><td>2-2</td></tr></tbody><tfoot><tr><td>Footer 1</td><td>Footer 2</td></tr></tfoot></table></div>',
        '<div><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td></tr><tr><td rowspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">2-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">2-2</td></tr></tbody><tfoot><tr><td class="ecl-table__cell" data-ecl-table-header="Column 1">Footer 1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">Footer 2</td></tr></tfoot></table></div>',
      ],
      'Table with vertical header - class is added' => [
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
      'Simple table without headers - leave as-is' => [
        '<table><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr><tr><td>3-1</td><td>3-2</td></tr></tbody></table>',
        '<table><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr><tr><td>3-1</td><td>3-2</td></tr></tbody></table>',
      ],
      'Table contains rowspan - leave as-is' => [
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td rowspan="2">1-1</td><td>1-2</td><td>1-3</td></tr><tr><td>2-2</td><td>2-3</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td rowspan="2">1-1</td><td>1-2</td><td>1-3</td></tr><tr><td>2-2</td><td>2-3</td></tr></tbody></table>',
      ],
      'Table contains colspan - leave as-is' => [
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td colspan="2">1-1</td><td>1-3</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td colspan="2">1-1</td><td>1-3</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>',
      ],
      'Table with multi header - leave as-is' => [
        '<table><thead><tr><th>Column 1</th><th colspan="2">Columns 2 - 3</th><th>Column 4</th></tr><tr><th></th><th>Column 2</th><th>Column 3</th><th></th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td><td>1-3</td><td>1-4</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td><td>2-4</td></tr></tbody></table>',
        '<table><thead><tr><th>Column 1</th><th colspan="2">Columns 2 - 3</th><th>Column 4</th></tr><tr><th></th><th>Column 2</th><th>Column 3</th><th></th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td><td>1-3</td><td>1-4</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td><td>2-4</td></tr></tbody></table>',
      ],
    ];
  }

}
