<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_inpage_navigation\Functional\Plugin\EntityMetaRelation;

use Drupal\oe_theme_inpage_navigation\InPageNavigationHelper;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Test Inpage navigation entity meta plugin form.
 */
class InPageNavigationTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * The node type id.
   *
   * @var string
   */
  protected $type;

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

    // Create content type.
    $this->type = 'test_content_type';
    $this->drupalCreateContentType(['name' => 'Test node type', 'type' => $this->type]);

    /** @var \Drupal\emr\EntityMetaRelationInstaller $installer */
    $installer = \Drupal::service('emr.installer');
    $installer->installEntityMetaTypeOnContentEntityType('oe_theme_inpage_navigation', 'node', [$this->type]);

  }

  /**
   * Test form of inpage navigation entity meta relation plugin.
   */
  public function testInPageNavigationForm(): void {
    $entity_meta_storage = \Drupal::entityTypeManager()->getStorage('entity_meta');

    // Assert we have no entity metas with the inpage navigation plugin.
    $this->assertCount(0, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));

    $this->drupalLogin($this->rootUser);
    $this->drupalGet('/node/add/' . $this->type);
    $this->assertElementPresent('.form-item-inpage-navigation input#edit-inpage-navigation');
    $this->assertSession()->checkboxNotChecked('Enable Inpage navigation');
    $this->assertSession()->pageTextContainsOnce('Show this content with vertical menu containing (anchored) links to H2-headings on long content pages.');

    // Create the node, without enabled the inpage navigation.
    $this->getSession()->getPage()->fillField('Title', 'The title of the page');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContainsOnce('Test node type The title of the page has been created.');

    $node = $this->getNodeByTitle('The title of the page');

    // Assert we still have no entity metas with the inpage_navigation plugin.
    $this->assertCount(0, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertFalse(InPageNavigationHelper::isInPageNavigation($node));

    $this->drupalGet($node->toUrl('edit-form'));
    $this->getSession()->getPage()->checkField('Enable Inpage navigation');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert we now have one entity metas with the inpage_navigation plugin.
    $node = $this->getNodeByTitle('The title of the page', TRUE);
    $this->assertCount(1, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertTrue(InPageNavigationHelper::isInPageNavigation($node));

    $this->drupalGet($node->toUrl('edit-form'));
    $this->getSession()->getPage()->uncheckField('Enable Inpage navigation');
    $this->getSession()->getPage()->pressButton('Save');

    // We still have one entity meta with the inpage_navigation plugin, as a new
    // revision got created.
    $node = $this->getNodeByTitle('The title of the page', TRUE);
    $this->assertCount(1, $entity_meta_storage->loadByProperties(['bundle' => 'oe_theme_inpage_navigation']));
    $this->assertFalse(InPageNavigationHelper::isInPageNavigation($node));
  }

}
