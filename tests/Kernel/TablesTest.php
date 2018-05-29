<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of the table component.
 */
class TablesTest extends AbstractKernelTestBase {

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
    $header = ['Name', 'Registration date', 'Email'];
    $build[] = [
      '#theme' => 'table',
      '#caption' => 'Default table',
      '#header' => $header,
      '#rows' => $rows_data,
    ];

    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);

    // Assert the count of tables.
    $table = $crawler->filter('table.ecl-table.ecl-table--responsive');
    $this->assertCount(1, $table);
    $this->assertArraySubset([
      '#attached' => [
        'library' => [
          'oe_theme/tables',
        ],
      ],
    ], $build);
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
          ['John Doe', '01/01/2016', 'john.doe@mail.com'],
        ],
      ],
      '3 rows' => [
        [
          ['John Doe', '01/01/2016', 'john.doe@mail.com'],
          ['Jane Doe', '06/12/2016', 'jane.doe@mail.com'],
          ['Jack Doe', '03/05/2017', 'jack.doe@mail.com'],
        ],
      ],
    ];
  }

}
