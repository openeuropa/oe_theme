<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class MenuLocalTasks.
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
            'title' => 'Active link',
            'url' => Url::fromUri('http://www.active.com'),
          ],
          '#active' => TRUE,
        ],
        'link2.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Inactive link',
            'url' => Url::fromUri('http://www.inactive.com'),
          ],
          '#active' => FALSE,
        ],
      ],
      '#user' => $this->user,
    ];

    $html = $this->renderRoot($render);
    $crawler = new Crawler($html);

    // Assert wrapper contains ECL class.
    $actual = $crawler->filter('nav.ecl-navigation-list-wrapper');
    $this->assertCount(1, $actual);

    // Assert list contains ECL classes.
    $actual = $crawler->filter('ul.ecl-navigation-list.ecl-navigation-list--tabs');
    $this->assertCount(1, $actual);

    // Assert active link contains ECL classes.
    $actual = $crawler->filter('a.ecl-navigation-list__link--active')->text();
    $this->assertEquals('Active link', trim($actual));

    // Assert regular link contains ECL classes.
    $actual = $crawler->filter('a.ecl-navigation-list__link')
      ->eq(0)
      ->text();
    $this->assertEquals('Active link', trim($actual));

    $actual = $crawler->filter('a.ecl-navigation-list__link')
      ->eq(1)
      ->text();
    $this->assertEquals('Inactive link', trim($actual));
  }

}
