<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class BreadcrumbTest.
 */
class BreadcrumbTest extends AbstractKernelTestBase {

  /**
   * Test a basic breadcrumb is themed using ECL breadcrumb component.
   *
   * @throws \Exception
   */
  public function testBreadcrumbRendering(): void {
    $links = [
      'Home' => '<front>',
      'Test' => '<front>',
      'Last' => '<front>',
    ];

    $breadcrumb = new Breadcrumb();
    foreach ($links as $title => $url) {
      $breadcrumb->addLink(Link::createFromRoute($title, $url));
    }
    $render_array = $breadcrumb->toRenderable();
    $html = $this->renderRoot($render_array);

    $crawler = new Crawler($html);

    // Assert wrapper contains ECL class.
    $actual = $crawler->filter('nav.ecl-breadcrumb');
    $this->assertCount(1, $actual);

    // Check if the number of rendered list item is correct.
    $li_item_count = $crawler->filter('ol.ecl-breadcrumb__segments-wrapper li');
    $this->assertCount(3, $li_item_count);

    // Check if the number of rendered links is correct.
    $links_count = $crawler->filter('ol.ecl-breadcrumb__segments-wrapper li a');
    $this->assertCount(2, $links_count);

    // Check if the number of rendered span is correct.
    $span_count = $crawler->filter('ol.ecl-breadcrumb__segments-wrapper li span');
    $this->assertCount(1, $span_count);

    // Check if the last element of the links is a span instead of an html link.
    $span = $crawler->filter('ol.ecl-breadcrumb__segments-wrapper li')->last();
    $this->assertEquals('Last', trim($span->text()));

    // Remove the last element which is not a link from the array.
    array_pop($links);

    // Assert that remaining links are rendered correctly.
    $position = 0;
    foreach ($links as $title => $url) {
      $link = $crawler->filter('ol.ecl-breadcrumb__segments-wrapper li.ecl-breadcrumb__segment a.ecl-breadcrumb__link')->eq($position);
      $this->assertEquals($title, trim($link->text()));
      $position++;
    }
  }

}
