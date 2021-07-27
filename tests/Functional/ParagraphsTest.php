<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests paragraphs forms.
 *
 * @group batch3
 */
class ParagraphsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'node',
    'oe_theme_helper',
    'oe_paragraphs_demo',
    'oe_content_timeline_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    \Drupal::service('theme_installer')->install(['oe_theme']);
    \Drupal::configFactory()
      ->getEditable('system.theme')
      ->set('default', 'oe_theme')
      ->save();

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();
  }

  /**
   * Test Accordion item paragraph form.
   */
  public function testAccordionItemParagraph(): void {
    // Add an user.
    $user = $this->drupalCreateUser([], '', TRUE);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_demo_landing_page');
    $page = $this->getSession()->getPage();
    $page->pressButton('Add Accordion');

    // Assert the title and body fields of Accordion item paragraph are shown
    // but the icon field is not.
    $this->assertSession()->fieldExists('field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text][0][value]');
    $this->assertSession()->fieldExists('field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text_long][0][value]');
    $this->assertSession()->fieldNotExists('field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_icon]');

    $values = [
      'title[0][value]' => 'Test Accordion',
      'field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text][0][value]' => 'Accordion item title',
      'field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text_long][0][value]' => 'Accordion item body',
      'oe_content_content_owner[0][target_id]' => 'Directorate-General for Informatics',
    ];
    $this->drupalPostForm(NULL, $values, 'Save');
    $this->drupalGet('/node/1');

    // Assert paragraph values are displayed.
    $this->assertSession()->pageTextContains('Accordion item title');
    $this->assertSession()->pageTextContains('Accordion item body');
  }

}
