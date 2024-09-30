<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Drupal\Tests\oe_theme\Traits\RequestTrait;
use Drupal\entity_test\Entity\EntityTest;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that the breadcrumb is properly displayed.
 *
 * @group batch3
 */
class BreadcrumbTest extends EntityKernelTestBase {

  use RequestTrait;
  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
    'image',
    'breakpoint',
    'responsive_image',
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

    // @todo Drupal 9 ignores settings in settings.testing.php in kernel tests.
    // See https://www.drupal.org/project/drupal/issues/3190974. Need to
    // skip node_modules directory during template scanning because wrong
    // template files are found (for example,
    // node_modules/@ecl/twig-component-description-list/description-list.html.twig
    // instead of templates/field/description-list.html.twig
    $settings = Settings::getAll();
    $settings['file_scan_ignore_directories'] = ['node_modules'];
    new Settings($settings);

    // Call the install hook of the User module which creates the Anonymous user
    // and User 1. This is needed because the Anonymous user is loaded to
    // provide the current User context which is needed in places like route
    // enhancers.
    // @see CurrentUserContext::getRuntimeContexts().
    // @see EntityConverter::convert().
    \Drupal::moduleHandler()->loadInclude('user', 'install');
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
    $actual = $crawler->filter('nav.ecl-breadcrumb');
    $this->assertCount(1, $actual);

    // Check if the number of rendered list item is correct.
    $li_item_count = $crawler->filter('ol.ecl-breadcrumb__container li.ecl-breadcrumb__segment');
    $this->assertCount(3, $li_item_count);

    // Check if the number of rendered links is correct.
    $links_count = $crawler->filter('ol.ecl-breadcrumb__container li.ecl-breadcrumb__segment a.ecl-breadcrumb__link');
    $this->assertCount(2, $links_count);

    // Assert that the last element is the current page.
    $current_page = $crawler->filter('ol.ecl-breadcrumb__container li.ecl-breadcrumb__current-page');
    $this->assertCount(1, $current_page);
    $this->assertEquals('Last', trim($current_page->text()));

    // Remove the last element which is not a link from the array.
    array_pop($links);

    // Assert that remaining links are rendered correctly.
    $position = 0;
    foreach ($links as $title => $url) {
      $link = $crawler->filter('ol.ecl-breadcrumb__container li.ecl-breadcrumb__segment a.ecl-breadcrumb__link')->eq($position);
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

    // Assert that the last element is the current page.
    $current_page = $crawler->filter('ol.ecl-breadcrumb__container li.ecl-breadcrumb__current-page');
    $this->assertCount(1, $current_page);
    $this->assertEquals('Test full view mode', trim($current_page->text()));
  }

}
