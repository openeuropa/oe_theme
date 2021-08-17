<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Drupal\Tests\oe_theme\Traits\RequestTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that the breadcrumb is properly displayed.
 *
 * @group batch2
 */
class BreadcrumbTest extends EntityKernelTestBase {

  use RequestTrait;
  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
    'image',
    'breakpoint',
    'responsive_image',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installConfig(['system', 'image', 'responsive_image']);

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Call the install hook of the User module which creates the Anonymous user
    // and User 1. This is needed because the Anonymous user is loaded to
    // provide the current User context which is needed in places like route
    // enhancers.
    // @see CurrentUserContext::getRuntimeContexts().
    // @see EntityConverter::convert().
    module_load_include('install', 'user');
    user_install();
  }

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
    $actual = $crawler->filter('nav.ecl-breadcrumb-core');
    $this->assertCount(1, $actual);

    // Check if the number of rendered list item is correct.
    $li_item_count = $crawler->filter('ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__segment');
    $this->assertCount(3, $li_item_count);

    // Check if the number of rendered links is correct.
    $links_count = $crawler->filter('ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__segment a.ecl-breadcrumb-core__link');
    $this->assertCount(2, $links_count);

    // Check if the number of rendered span is correct and
    // Check if the last element of the links is a span instead of an html link.
    $current_page = $crawler->filter('ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__current-page');
    $this->assertCount(1, $current_page);
    $this->assertEquals('Last', trim($current_page->text()));

    // Remove the last element which is not a link from the array.
    array_pop($links);

    // Assert that remaining links are rendered correctly.
    $position = 0;
    foreach ($links as $title => $url) {
      $link = $crawler->filter('ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__segment a.ecl-breadcrumb-core__link')->eq($position);
      $this->assertEquals($title, trim($link->text()));
      $position++;
    }
  }

  /**
   * Test the title into the breadcrumb.
   */
  public function testBreadcrumbTitleRendering(): void {
    $entity = EntityTest::create();
    $entity->save();
    // Simulate a request to the node.
    $this->setCurrentRequest('/entity_test/' . $entity->id());

    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
    $render_array = $breadcrumb->toRenderable();

    $html = $this->renderRoot($render_array);
    $crawler = new Crawler($html);

    // Check if the number of rendered span is correct and
    // Check if the last element of the links is filled with the page title.
    $current_page = $crawler->filter('ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__current-page');
    $this->assertCount(1, $current_page);
    $this->assertEquals('Test full view mode', trim($current_page->text()));
  }

}
