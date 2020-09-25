<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\media\MediaInterface;

/**
 * Base class for testing content types.
 */
abstract class ContentRenderTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
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
   * Creates media document entity.
   *
   * @param string $name
   *   Name of the document media.
   *
   * @return \Drupal\media\MediaInterface
   *   Media document instance.
   */
  protected function createMediaDocument(string $name): MediaInterface {
    // Create file instance.
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), "public://sample_$name.pdf");
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'document',
      'name' => "Test document $name",
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Gets the entity type's storage.
   *
   * @param string $entity_type_id
   *   The entity type ID to get a storage for.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The entity type's storage.
   */
  protected function getStorage(string $entity_type_id): EntityStorageInterface {
    return \Drupal::entityTypeManager()->getStorage($entity_type_id);
  }

  /**
   * Asserts rendering of Media Document Default view mode.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $name
   *   Name of the document.
   */
  protected function assertMediaDocumentDefaultRender(NodeElement $element, string $name): void {
    // Assert documents file.
    $file_wrapper = $element->find('css', 'div.ecl-file');
    $file_row = $file_wrapper->find('css', '.ecl-file .ecl-file__container');
    $file_title = $file_row->find('css', '.ecl-file__title');
    $this->assertContains("Test document $name", $file_title->getText());
    $file_info_language = $file_row->find('css', '.ecl-file__info div.ecl-file__language');
    $this->assertContains('English', $file_info_language->getText());
    $file_info_properties = $file_row->find('css', '.ecl-file__info div.ecl-file__meta');
    $this->assertContains('(2.96 KB - PDF)', $file_info_properties->getText());
    $file_download_link = $file_row->find('css', '.ecl-file__download');
    $this->assertContains("/sample_$name.pdf", $file_download_link->getAttribute('href'));
    $this->assertContains('Download', $file_download_link->getText());
  }

}
