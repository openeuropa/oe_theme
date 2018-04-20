<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of the table component.
 */
class TablesTest extends AbstractKernelTest {

  /**
   * Tests a single table rendering.
   *
   * @param array $rows_data
   *   Data to render a table rows.
   *
   * @throws \Exception
   *   Thrown on rendering errors.
   *
   * @dataProvider singleTableDataProvider
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function testSingleTable(array $rows_data): void {
    $header = ['#', 'Name', 'e-mail address'];
    $build[] = [
      '#theme' => 'table',
      '#caption' => 'The table caption / Title',
      '#header' => $header,
      '#rows' => $rows_data,
    ];

    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);

    // Assert the count of tables.
    $table = $crawler->filter('table.ecl-table');
    $this->assertCount(1, $table);
  }

  /**
   * Data provider for the single table test.
   *
   * @return array
   *   An array of table test cases. Each case contains rows data.
   */
  public function singleTableDataProvider(): array {
    return [
      '1 row' => [
        [
          [1, 'Name 01', 'mail-01@example.com'],
        ],
      ],
      '2 rows' => [
        [
          [1, 'Name 01', 'mail-01@example.com'],
          [2, 'Name 02', 'mail-02@example.com'],
        ],
      ],
    ];
  }

}
