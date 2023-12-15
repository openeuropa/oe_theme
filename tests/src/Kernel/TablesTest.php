<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

/**
 * Tests the rendering of the table component.
 *
 * @group batch2
 */
class TablesTest extends AbstractKernelTestBase {

  /**
   * Tests a single table rendering.
   *
   * @param array $structure
   *   Data to render a table rows.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *   Thrown on rendering errors.
   *
   * @dataProvider dataProvider
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function testSingleTable(array $structure, array $assertions): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
    $build = array_merge(['#theme' => 'table', '#caption' => 'Default table'], $structure);

    $html = $this->renderRoot($build);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for rendering tests.
   *
   * The actual data is read from fixtures stored in a YAML configuration.
   *
   * @return array
   *   A set of dump data for testing.
   */
  public function dataProvider(): array {
    return $this->getFixtureContent('table_rendering.yml');
  }

}
