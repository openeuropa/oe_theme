<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that the main menu is properly displayed.
 *
 * @group batch2
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
  protected function setUp(): void {
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

    $leaf = MenuLinkContent::create([
      'title' => 'Leaf item',
      'link' => ['uri' => 'http://leaf.eu'],
      'menu_name' => 'main',
      'expanded' => TRUE,
    ]);
    $leaf->save();

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

    $parameters = new MenuTreeParameters();
    $parameters->setActiveTrail([
      "menu_link_content:" . $parent->uuid(),
      "menu_link_content:" . $child->uuid(),
    ]);
    $tree = $menu_tree->load('main', $parameters);
    $build = $menu_tree->build($tree);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // Assert wrapper contains ECL class.
    $actual = $crawler->filter('nav.ecl-menu');
    $this->assertCount(1, $actual);

    // Assert that there are two parent links but only one active link.
    $links = $crawler->filter('nav.ecl-menu li.ecl-menu__item a.ecl-menu__link');
    $this->assertEquals(2, $links->count());
    $active_link = $crawler->filter('nav.ecl-menu li.ecl-menu__item.ecl-menu__item--current a.ecl-menu__link');
    $this->assertEquals(1, $active_link->count());

    // Assert that parent link is correctly rendered.
    // Remove all non-printable characters.
    $this->assertEquals('Parent item', preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($active_link->text())));
    $this->assertEquals('http://parent.eu', trim($active_link->extract(['href'])[0]));

    // Assert children are rendered correctly.
    $position = 0;
    foreach ($children as $title => $url) {
      $link = $crawler->filter('.ecl-menu__mega li.ecl-menu__subitem a.ecl-menu__sublink')->eq($position);
      $this->assertEquals($title, trim($link->text()));
      $this->assertEquals($url, trim($link->extract(['href'])[0]));
      $position++;
    }

    // Assert the last children is active.
    $link = $crawler->filter('.ecl-menu__mega li.ecl-menu__subitem.ecl-menu__subitem--current a.ecl-menu__sublink');
    $this->assertEquals(1, $link->count());
    $this->assertEquals('Child 3', trim($link->text()));
    $this->assertEquals('http://child-3.eu', trim($link->extract(['href'])[0]));
  }

}
