<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Url;
use Drupal\Tests\oe_theme\PatternAssertions\TabsAssert;

/**
 * Tests that Drupal local tasks are properly rendered.
 *
 * @group batch3
 */
class MenuLocalTasksTest extends AbstractKernelTestBase {

  /**
   * Test menu local tasks.
   *
   * @throws \Exception
   */
  public function testMenuLocalTasks(): void {
    $render = [
      '#theme' => 'menu_local_tasks',
      '#primary' => [
        'link1.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Third link - Active',
            'url' => Url::fromUri('http://www.active.com'),
          ],
          '#active' => TRUE,
          '#weight' => 10,
        ],
        'link2.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'First link - Inactive',
            'url' => Url::fromUri('http://www.inactive.com'),
          ],
          '#active' => FALSE,
          '#weight' => -10,
        ],
        'link3.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Second link',
            'url' => Url::fromUri('http://www.middlelink.com'),
          ],
          '#active' => FALSE,
          '#weight' => 0,
        ],
      ],
    ];

    $html = $this->renderRoot($render);
    $expected_items = [
      'items' => [
        [
          'label' => 'First link - Inactive',
          'path' => 'http://www.inactive.com',
        ],
        [
          'label' => 'Second link',
          'path' => 'http://www.middlelink.com',
        ],
        [
          'label' => 'Third link - Active',
          'path' => 'http://www.active.com',
          'is_current' => TRUE,
        ],
      ],
    ];
    $assert = new TabsAssert();
    $assert->assertPattern($expected_items, $html);
  }

}
