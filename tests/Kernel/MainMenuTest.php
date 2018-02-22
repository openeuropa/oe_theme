<?php

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class MainMenuTest.
 */
class MainMenuTest extends AbstractKernelTest {

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
   * Test main menu is themed using ECL navigation menu.
   *
   * @throws \Exception
   */
  public function testMainMenuRendering() {

    $menu_tree = \Drupal::menuTree();
    $menu_link_content = MenuLinkContent::create([
      'link' => ['uri' => 'route:<none>'],
      'menu_name' => 'main',
      'title' => 'Link test',
    ]);
    $menu_link_content->save();
    $tree = $menu_tree->load('main', new MenuTreeParameters());
    $build = $menu_tree->build($tree);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // Assert wrapper contains ECL class.
    $actual = $crawler->filter('nav.ecl-navigation-menu')->count();
    $this->assertEquals(1, $actual);

    // Assert link is correctly rendered.
    $actual = $crawler->filter('nav.ecl-navigation-menu a.ecl-navigation-menu__link')->text();
    $this->assertEquals('Link test', trim($actual));
  }

}
