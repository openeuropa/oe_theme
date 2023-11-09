<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that Drupal local tasks are properly rendered.
 *
 * @group batch2
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
    $crawler = new Crawler($html);

    // Assert wrapper contains ECL class.
    $actual = $crawler->filter('nav.ecl-navigation');
    $this->assertCount(1, $actual);

    // Assert list contains ECL classes.
    $actual = $crawler->filter('ul.ecl-navigation.ecl-u-pb-m.ecl-u-pt-m');
    $this->assertCount(1, $actual);

    // Assert active link contains ECL classes.
    $actual = $crawler->filter('li.ecl-navigation__item--active')->text();
    $this->assertEquals('Third link - Active', trim($actual));

    // Assert regular link contains ECL classes and the links are ordered by
    // weight.
    $actual = $crawler->filter('li.ecl-navigation__item > a')
      ->eq(0)
      ->text();
    $this->assertEquals('First link - Inactive', trim($actual));

    $actual = $crawler->filter('li.ecl-navigation__item > a')
      ->eq(1)
      ->text();
    $this->assertEquals('Second link', trim($actual));

    $actual = $crawler->filter('li.ecl-navigation__item > a')
      ->eq(2)
      ->text();
    $this->assertEquals('Third link - Active', trim($actual));
  }

}
