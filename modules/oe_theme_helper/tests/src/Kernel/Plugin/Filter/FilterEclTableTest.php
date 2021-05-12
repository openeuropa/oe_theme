<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Filter;

use Drupal\KernelTests\KernelTestBase;
use Drupal\filter\FilterPluginCollection;

/**
 * Test FilterEclTable plugin.
 *
 * @coversDefaultClass \Drupal\oe_theme_helper\Plugin\Filter\FilterEclTable
 *
 * @group batch2
 */
class FilterEclTableTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'filter', 'oe_theme_helper'];

  /**
   * FilterEclTable object.
   *
   * @var \Drupal\filter\Plugin\FilterInterface
   */
  protected $filter;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system']);

    // Get FilterEclTable object.
    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $filters = $bag->getAll();
    $this->filter = $filters['filter_ecl_table'];
  }

  /**
   * Tests the "ECL table support" filter.
   *
   * @covers ::process
   */
  public function testEclTableFilter(): void {
    $tests = $this->getTestData();

    foreach ($tests as $source => $value) {
      $result = $this->filter->process($source, $this->filter)->getProcessedText();
      $this->assertStringContainsString($value, $result);
    }
  }

  /**
   * Gets raw data to filter and expected results.
   *
   * @return array
   *   An associative array, whereas each key is an arbitrary input string and
   *   each value is an expected result.
   */
  protected function getTestData(): array {
    return [
      // Simple table with header - class and data-ecl-table-header are added.
      '<p>Some text before table</p><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1">1-1</td><td>1-2</td></tr><tr><td rowspan="1">2-1</td><td>2-2</td></tr></tbody></table><p>Some text after table</p>' =>
      '<p>Some text before table</p><table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td colspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">1-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td></tr><tr><td rowspan="1" class="ecl-table__cell" data-ecl-table-header="Column 1">2-1</td><td class="ecl-table__cell" data-ecl-table-header="Column 2">2-2</td></tr></tbody></table><p>Some text after table</p>',
      // Table with vertical header - class is added.
      '<table><tbody><tr><th>Row 1</th><td>1-1</td><td>1-2</td></tr><tr><th>Row 2</th><td>2-1</td><td>2-2</td></tr><tr><th>Row 3</th><td>3-1</td><td>3-2</td></tr></tbody></table>' =>
      '<table><tbody><tr><th class="ecl-table__cell">Row 1</th><td class="ecl-table__cell">1-1</td><td class="ecl-table__cell">1-2</td></tr><tr><th class="ecl-table__cell">Row 2</th><td class="ecl-table__cell">2-1</td><td class="ecl-table__cell">2-2</td></tr><tr><th class="ecl-table__cell">Row 3</th><td class="ecl-table__cell">3-1</td><td class="ecl-table__cell">3-2</td></tr></tbody></table',
      // Table with horizontal and vertical headers.
      '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><th>Row 1</th><td>1-2</td><td>1-3</td></tr><tr><th>Row 2</th><td>2-2</td><td>2-3</td></tr></tbody></table>' =>
      '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><th class="ecl-table__cell">Row 1</th><td class="ecl-table__cell" data-ecl-table-header="Column 2">1-2</td><td class="ecl-table__cell" data-ecl-table-header="Column 3">1-3</td></tr><tr><th class="ecl-table__cell">Row 2</th><td class="ecl-table__cell" data-ecl-table-header="Column 2">2-2</td><td class="ecl-table__cell" data-ecl-table-header="Column 3">2-3</td></tr></tbody></table>',
      // Simple table without headers - leave as-is.
      '<table><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr><tr><td>3-1</td><td>3-2</td></tr></tbody></table>' =>
      '<table><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr><tr><td>3-1</td><td>3-2</td></tr></tbody></table>',
      // Table contains rowspan - leave as-is.
      '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td rowspan="2">1-1</td><td>1-2</td><td>1-3</td></tr><tr><td>2-2</td><td>2-3</td></tr></tbody></table>' =>
      '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td rowspan="2">1-1</td><td>1-2</td><td>1-3</td></tr><tr><td>2-2</td><td>2-3</td></tr></tbody></table>',
      // Table contains colspan - leave as-is.
      '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td colspan="2">1-1</td><td>1-3</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>' =>
      '<table><thead><tr><th>Column 1</th><th>Column 2</th><th>Column 3</th></tr></thead><tbody><tr><td colspan="2">1-1</td><td>1-3</td></tr><tr><td>2-1</td><td>2-2</td><td>2-3</td></tr></tbody></table>',
      // Table without tbody - leave as-is.
      '<table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></table>' =>
      '<table><caption>Caption</caption><thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></table>',
      // Broken table - leave as-is.
      '<table<thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></tbody></table>' =>
      '<table><tr><th>Column 1</th><th>Column 2</th></tr><tbody><tr><td>1-1</td><td>1-2</td></tr><tr><td>2-1</td><td>2-2</td></tr></tbody></table>',
    ];
  }

}
