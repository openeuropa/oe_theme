<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests the page header block.
 */
class PageHeaderBlockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'entity_test',
    'oe_theme_helper',
    'page_header_metadata_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Enable oe_theme and set it as default.
    $this->assertTrue($this->container->get('theme_installer')->install(['oe_theme']));
    $this->container->get('config.factory')
      ->getEditable('system.theme')
      ->set('default', 'oe_theme')
      ->save();

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    $this->container->get('plugin.manager.ui_patterns')->clearCachedDefinitions();

    // Allow anonymous users to view test entities.
    $this->grantPermissions(Role::load(RoleInterface::ANONYMOUS_ID), ['view test entity']);
  }

  /**
   * Tests the page header block rendering and context system.
   */
  public function testRendering(): void {
    // Create a test entity.
    $entity = EntityTest::create([
      'name' => $this->randomString(),
    ]);
    $entity->save();
    $this->drupalGet($entity->toUrl());

    $assert_session = $this->assertSession();

    // Only one page header should be rendered.
    $assert_session->elementsCount('css', '.ecl-page-header', 1);
    $header = $this->getSession()->getPage()->find('css', '.ecl-page-header');
    // Test that the page title is rendered in the page header.
    $this->assertEquals($entity->label(), trim($header->find('css', '.ecl-page-header__title')->getText()));
    // Site identity is not shown by default.
    $assert_session->elementsCount('css', '.ecl-page-header__identity', 0, $header);
    // Intro and meta items are empty.
    $assert_session->elementsCount('css', '.ecl-page-header__intro', 0);
    $assert_session->elementsCount('css', '.ecl-meta--header .ecl-meta__item', 0);

    // Test another route.
    $this->drupalGet('/user/login');
    $assert_session->elementsCount('css', '.ecl-page-header', 1);
    $header = $this->getSession()->getPage()->find('css', '.ecl-page-header');
    $this->assertEquals('Log in', trim($header->find('css', '.ecl-page-header__title')->getText()));
    $assert_session->elementsCount('css', '.ecl-page-header__description', 0);
    $assert_session->elementsCount('css', '.ecl-page-header__meta-list', 0);
    $assert_session->elementsCount('css', '.ecl-page-header__info-item', 0);

    // Enable the test plugin and add some metadata.
    $test_data = [
      'title' => 'Custom page title.',
      'identity' => 'Custom site identity',
      'introduction' => 'Custom page introduction.',
      'metas' => [
        'Custom meta 1',
        'Custom meta 2',
        'Custom meta 3',
      ],
      'infos' => [
        ['text' => 'Monday 8 February'],
        ['text' => 'Munich, Germany'],
      ],
    ];
    $this->container->get('state')->set('page_header_test_plugin_applies', TRUE);
    $this->container->get('state')->set('page_header_test_plugin_metadata', $test_data);

    // Invalidate the page caches.
    $this->container->get('cache.page')->deleteAll();
    $this->container->get('cache.dynamic_page_cache')->deleteAll();

    // Reload the page.
    $this->drupalGet('/user/login');
    // The test plugin metadata is shown as it has higher priority than the
    // default one.
    $assert_session->elementsCount('css', '.ecl-page-header', 1);
    $assert_session->elementsCount('css', '.ecl-page-header__description', 1);
    $assert_session->elementsCount('css', '.ecl-page-header__meta-list', 1);
    $assert_session->elementsCount('css', '.ecl-page-header__info-item', 2);
    $header = $this->getSession()->getPage()->find('css', '.ecl-page-header');
    $this->assertEquals($test_data['title'], trim($header->find('css', '.ecl-page-header__title')->getText()));
    $this->assertEquals($test_data['introduction'], trim($header->find('css', '.ecl-page-header__description')->getText()));

    $metas = '';
    foreach ($test_data['metas'] as $meta) {
      $metas .= ($metas != '' ? ' | ' : '') . $meta;
    }
    $this->assertEquals($metas, trim($header->find('css', '.ecl-page-header__meta-list')->getText()));

    $infos = array_map(function (NodeElement $element) {
      return ['text' => trim($element->getText())];
    }, $header->findAll('css', '.ecl-page-header__info-item'));
    $this->assertEquals($test_data['infos'], $infos);
  }

}
