<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests paragraphs forms.
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
    ];
    $this->drupalPostForm(NULL, $values, 'Save');
    $this->drupalGet('/node/1');

    // Assert paragraph values are displayed.
    $this->assertSession()->pageTextContains('Accordion item title');
    $this->assertSession()->pageTextContains('Accordion item body');
  }

}
