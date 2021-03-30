<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_inpage_navigation\FunctionalJavascript\Plugin\EntityMetaRelation;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\oe_theme_inpage_navigation\InPageNavigationHelper;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Test Inpage navigation entity meta plugin form.
 */
class InPageNavigationTest extends WebDriverTestBase {

  use NodeCreationTrait;

  /**
   * The node type id for ct with in-page nav.
   *
   * @var string
   */
  protected $typeWithInpageNav;

  /**
   * The node type id for ct without in-page nav.
   *
   * @var string
   */
  protected $typeWithoutInpageNav;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_theme_inpage_navigation',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create content type without in-page navigation.
    $this->typeWithoutInpageNav = 'test_content_type_without_inpage';
    $this->drupalCreateContentType(['name' => 'Test node type', 'type' => $this->typeWithoutInpageNav]);

    // Create content type with in-page navigation.
    $this->typeWithInpageNav = 'test_content_type_with_inpage';
    $ct_with = $this->drupalCreateContentType(['name' => 'Test node type', 'type' => $this->typeWithInpageNav]);
    $ct_with->setThirdPartySetting('oe_theme_inpage_navigation', 'enabled', TRUE);
    $ct_with->save();

    /** @var \Drupal\emr\EntityMetaRelationInstaller $installer */
    $installer = \Drupal::service('emr.installer');
    $installer->installEntityMetaTypeOnContentEntityType('oe_theme_inpage_navigation', 'node', [$this->typeWithInpageNav, $this->typeWithoutInpageNav]);
  }

  /**
   * Test form of inpage navigation entity meta relation plugin.
   */
  public function testInPageNavigationForm(): void {
    $entity_meta_storage = \Drupal::entityTypeManager()->getStorage('entity_meta');
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    // Assert we have no entity metas with the inpage navigation plugin.
    $this->assertCount(0, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));

    $this->drupalLogin($this->rootUser);
    $this->drupalGet('/node/add/' . $this->typeWithoutInpageNav);
    $this->assertElementPresent('.form-item-inpage-navigation input#edit-inpage-navigation');
    $this->clickLink('In-page Navigation');
    $this->assertSession()->checkboxNotChecked('Override default settings');
    $this->assertSession()->pageTextContains('Disabled by default');
    $this->assertFalse($this->getSession()->getPage()->findField('Enable in-page navigation on this page')->isVisible());
    $this->assertSession()->checkboxNotChecked('Enable in-page navigation on this page');

    // Create the node, without enabling the inpage navigation.
    $this->getSession()->getPage()->fillField('Title', 'The title of the page');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContainsOnce('Test node type The title of the page has been created.');

    $node = $this->getNodeByTitle('The title of the page');

    // Assert we still have no entity metas with the inpage_navigation plugin.
    $this->assertCount(0, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertFalse(InPageNavigationHelper::isInPageNavigationEnabled($node));

    $this->drupalGet($node->toUrl('edit-form'));
    $this->clickLink('In-page Navigation');
    $this->getSession()->getPage()->checkField('Override default settings');
    $this->getSession()->getPage()->checkField('Enable in-page navigation on this page');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert we now have one entity metas with the inpage_navigation plugin.
    $node = $this->getNodeByTitle('The title of the page', TRUE);
    $this->assertCount(1, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertTrue(InPageNavigationHelper::isInPageNavigationEnabled($node));

    $this->drupalGet($node->toUrl('edit-form'));
    $this->clickLink('In-page Navigation');
    $this->assertTrue($this->getSession()->getPage()->findField('Enable in-page navigation on this page')->isVisible());
    $this->getSession()->getPage()->uncheckField('Enable in-page navigation on this page');
    $this->getSession()->getPage()->pressButton('Save');

    // We still have one entity meta with the inpage_navigation plugin, as a new
    // revision got created.
    $node = $this->getNodeByTitle('The title of the page', TRUE);
    $this->assertCount(1, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertFalse(InPageNavigationHelper::isInPageNavigationEnabled($node));

    // Test the other content type.
    $this->drupalGet('/node/add/' . $this->typeWithInpageNav);
    $this->assertElementPresent('.form-item-inpage-navigation input#edit-inpage-navigation');
    $this->clickLink('In-page Navigation');
    $this->assertSession()->checkboxNotChecked('Override default settings');
    $this->assertSession()->pageTextContains('Enabled by default');
    $this->assertSession()->checkboxNotChecked('Enable in-page navigation on this page');

    // Create the node, with the inpage navigation enabled by default.
    $this->getSession()->getPage()->fillField('Title', 'The title of the page with in-page navigation');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContainsOnce('Test node type The title of the page with in-page navigation has been created.');

    $node_with_navbar = $this->getNodeByTitle('The title of the page with in-page navigation');

    // Assert we keep having same number of entity metas, none was created.
    $this->assertCount(1, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertTrue(InPageNavigationHelper::isInPageNavigationEnabled($node_with_navbar));

    $this->drupalGet($node_with_navbar->toUrl('edit-form'));
    $this->clickLink('In-page Navigation');
    $this->getSession()->getPage()->checkField('Override default settings');
    $this->getSession()->getPage()->uncheckField('Enable in-page navigation on this page');
    $this->getSession()->getPage()->pressButton('Save');

    $node_storage->resetCache([$node_with_navbar->id()]);
    $node_without_navbar = $this->getNodeByTitle('The title of the page with in-page navigation');
    $this->assertCount(2, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertFalse(InPageNavigationHelper::isInPageNavigationEnabled($node_without_navbar));

    // Edit again and enable in-page navigation.
    $this->drupalGet($node_with_navbar->toUrl('edit-form'));
    $this->clickLink('In-page Navigation');
    $this->getSession()->getPage()->checkField('Override default settings');
    $this->getSession()->getPage()->checkField('Enable in-page navigation on this page');
    $this->getSession()->getPage()->pressButton('Save');

    $node_storage->resetCache([$node_with_navbar->id()]);
    $node_without_navbar = $this->getNodeByTitle('The title of the page with in-page navigation');
    $this->assertCount(2, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertTrue(InPageNavigationHelper::isInPageNavigationEnabled($node_without_navbar));

    // Edit again and disable override, should be still visible.
    $this->drupalGet($node_with_navbar->toUrl('edit-form'));
    $this->clickLink('In-page Navigation');
    $this->getSession()->getPage()->uncheckField('Override default settings');
    $this->assertFalse($this->getSession()->getPage()->findField('Enable in-page navigation on this page')->isVisible());
    $this->getSession()->getPage()->pressButton('Save');

    $node_storage->resetCache([$node_with_navbar->id()]);
    $node_without_navbar = $this->getNodeByTitle('The title of the page with in-page navigation');
    $this->assertCount(2, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertTrue(InPageNavigationHelper::isInPageNavigationEnabled($node_without_navbar));
  }

}
