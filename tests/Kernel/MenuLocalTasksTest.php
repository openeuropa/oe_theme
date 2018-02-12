<?php

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Url;

/**
 * Class MenuLocalTasks.
 */
class MenuLocalTasksTest extends AbstractKernelTest {

  /**
   * Test menu local tasks.
   */
  public function testMenuLocalTasks() {
    $this->markTestSkipped('Must be revisited when we found a proper way to test.');

    $render = [
      '#theme' => 'menu_local_tasks',
      '#primary' => [
        'link1.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Menu local task',
            'url' => Url::fromRoute('/'),
          ],
        ],
      ],
    ];

    $output = (string) \Drupal::service('renderer')->renderRoot($render);
  }

}
