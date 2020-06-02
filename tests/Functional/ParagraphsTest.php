<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that theme configuration is correctly applied.
 */
class ParagraphsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'node',
    'oe_theme_helper',
    'oe_paragraphs_demo',
    'oe_content_timeline_field',
  ];

  /**
   * Test Accordion item paragraph form.
   */
  public function testAccordionParagraph(): void {
    // Add an user.
    $user = $this->drupalCreateUser([], '', TRUE);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_demo_landing_page');
    $page = $this->getSession()->getPage();
    $page->pressButton('Add Accordion');

    // Assert the title and body fields of Accordion item paragraph are shown
    // but the icon field is not.
    $this->assertSession()->elementExists('css', '.field--name-field-oe-text');
    $this->assertSession()->elementExists('css', '.field--name-field-oe-text-long');
    $this->assertSession()->elementNotExists('css', '.field--name-field-oe-icon');

    $values = [
      'title[0][value]' => 'Test Accordion',
      'field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text][0][value]' => 'Accordion item title',
      'field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text_long][0][value]' => 'Accordion item body',
    ];
    $this->drupalPostForm(NULL, $values, 'Save');
    $this->drupalGet('/node/1');

    // Assert paragraph values are displayed.
    $this->assertSession()->pageTextContains('Accordion item title');
    $this->assertSession()->pageTextContains('Accordion item body');
  }

}
