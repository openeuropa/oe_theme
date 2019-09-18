<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class MainMenuTest.
 */
class MainMenuTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'menu_link_content',
    'link',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('menu_link_content');

    // Ensure that the weight of module_link_content is higher than system.
    // @see menu_link_content_install()
    module_set_weight('menu_link_content', 1);
  }

  /**
   * Test main menu is themed using ECL navigation menu component.
   *
   * @throws \Exception
   */
  public function testMainMenuRendering(): void {
    $menu_tree = \Drupal::menuTree();
    $parent = MenuLinkContent::create([
      'title' => 'Parent item',
      'link' => ['uri' => 'http://parent.eu'],
      'menu_name' => 'main',
      'expanded' => TRUE,
    ]);
    $parent->save();

    $children = [
      'Child 1' => 'http://child-1.eu',
      'Child 2' => 'http://child-2.eu',
      'Child 3' => 'http://child-3.eu',
    ];
    foreach ($children as $title => $url) {
      $child = MenuLinkContent::create([
        'title' => $title,
        'link' => ['uri' => $url],
        'parent' => $parent->getPluginId(),
        'menu_name' => 'main',
      ]);
      $child->save();
    }

    $tree = $menu_tree->load('main', new MenuTreeParameters());
    $build = $menu_tree->build($tree);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // Assert wrapper contains ECL class.
    $actual = $crawler->filter('nav.ecl-menu-legacy');
    $this->assertCount(1, $actual);

    // Assert that parent link is correctly rendered.
    $link = $crawler->filter('nav.ecl-menu-legacy a.ecl-menu-legacy__link')->first();
    // Remove all non-printable characters.
    $this->assertEquals('Parent item', preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($link->text())));
    $this->assertEquals('http://parent.eu', trim($link->extract(['href'])[0]));

    // Assert children are rendered correctly.
    $position = 0;
    foreach ($children as $title => $url) {
      $link = $crawler->filter('.ecl-menu-legacy__mega a.ecl-menu-legacy__sublink')->eq($position);
      $this->assertEquals($title, trim($link->text()));
      $this->assertEquals($url, trim($link->extract(['href'])[0]));
      $position++;
    }
  }

}
