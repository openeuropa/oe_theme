<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that our Call for tender (oe_tender) content type render.
 */
class ContentTendersRenderTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'path',
    'oe_theme_helper',
    'oe_theme_content_tender',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    \Drupal::service('theme_installer')->install(['oe_theme']);
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'oe_theme')->save();

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();
  }

  /**
   * Tests that the Tender page renders correctly.
   */
  public function testTenderRendering(): void {
    // Create a document for Tender results.
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test.pdf');
    $file->setPermanent();
    $file->save();

    $media = \Drupal::entityTypeManager()->getStorage('media')->create([
      'bundle' => 'document',
      'name' => 'Test document',
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
      ],
      'uid' => 0,
      'status' => 1,
    ]);

    $media->save();

    // Create a Call for tenders node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type' => 'oe_tender',
      'title' => 'Test tender node',
      'body' => 'Body',
      'oe_tender_deadline' => [
        'value' => date('Y') + 1 . '-06-10T23:30:00',
      ],
      'oe_documents' => [
        [
          'target_id' => (int) $media->id(),
        ],
      ],
      'oe_summary' => '100',
      'oe_tender_opening_date' => [
        'value' => '2020-04-30',
      ],
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_reference' => '100',
      'oe_departments' => '',
      'oe_teaser' => '',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    $this->drupalGet('/node/' . $node->id());

    /** @var Drupal\Tests\WebAssert $session */
    $session = $this->assertSession();

    $navigation = $session->elementExists('css', '.ecl-inpage-navigation');
    $navigation_items = $navigation->findAll('css', '.ecl-inpage-navigation__item a');
    $this->assertCount(3, $navigation_items);

    $content = $session->elementExists('css', '.ecl-col-lg-9');
    $session->elementsCount('css', '.ecl-col-lg-9', 1);

    // Assert Details group.
    $headings = $content->findAll('css', 'h2.ecl-u-type-heading-2.ecl-u-type-color-black');
    $this->assertEquals('Details', $headings[0]->getText());

    // Assert Description field.
    $this->assertEquals('Description', $headings[1]->getText());

    // Assert Documents field.
    $this->assertEquals('Documents', $headings[2]->getText());

    // Assert status OPEN.
    $this->assertEquals('open', $content->find('xpath', '//*[text() = "Status"]/following-sibling::dd[1]')->getText());
    // Assert NOT strike deadline.
    $this->assertFalse($content->find('xpath', '//*[text() = "Deadline date"]/following-sibling::dd/div')->hasClass('ecl-u-type-strike'));

    // Assert status UPCOMING.
    $node->set('oe_tender_opening_date', ['value' => date('Y') + 1 . '-05-31']);
    $node->set('oe_tender_deadline', ['value' => date('Y') + 1 . '-06-30T23:30:00'])->save();

    $this->drupalGet('/node/' . $node->id());

    $this->assertEquals('upcoming', $content->find('xpath', '//*[text() = "Status"]/following-sibling::dd[1]')->getText());
    // Assert NOT strike deadline.
    $this->assertFalse($content->find('xpath', '//*[text() = "Deadline date"]/following-sibling::dd/div')->hasClass('ecl-u-type-strike'));

    // Assert status CLOSED.
    $node->set('oe_tender_opening_date', ['value' => '2020-05-31']);
    $node->set('oe_tender_deadline', ['value' => '2020-05-31T23:30:00'])->save();

    $this->drupalGet('/node/' . $node->id());

    $this->assertEquals('closed', $content->find('xpath', '//*[text() = "Status"]/following-sibling::dd[1]')->getText());
    // Assert strike deadline.
    $content->find('xpath', '//*[text() = "Deadline date"]/following-sibling::dd/div')->hasClass('ecl-u-type-strike');

    // Assert status empty.
    $node->set('oe_tender_opening_date', ['value' => ''])->save();

    $this->drupalGet('/node/' . $node->id());

    $this->assertEquals('N/A', $content->find('xpath', '//*[text() = "Status"]/following-sibling::dd[1]')->getText());
    // Assert strike deadline.
    $content->find('xpath', '//*[text() = "Deadline date"]/following-sibling::dd/div')->hasClass('ecl-u-type-strike');
  }

}
