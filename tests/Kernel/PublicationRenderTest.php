<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\media\MediaInterface;

/**
 * Tests call for tenders rendering.
 */
class PublicationRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * Test a publication being rendered as a teaser.
   */
  public function testTeaser(): void {
    // Create a document for Publication.
    $media_document = $this->createMediaDocument('publication_document');

    // Create a Publication node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_teaser' => 'Test teaser text.',
      'oe_publication_type' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'oe_documents' => [$media_document],
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Test Publication node',
      'meta' => '15 April 2020',
      'description' => 'Test teaser text.',
    ];
    $assert->assertPattern($expected_values, $html);

    // Add thumbnail.
    $media_image = $this->createMediaImage('publication_image');
    $node->set('oe_publication_thumbnail', $media_image)->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values = [
      'title' => 'Test Publication node',
      'meta' => '15 April 2020',
      'image' => [
        'src' => 'styles/oe_theme_publication_thumbnail/public/placeholder_publication_image.png',
        'alt' => '',
      ],
    ];
    $assert->assertPattern($expected_values, $html);
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
      'oe_media_file_type' => 'local',
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
   * Creates media image entity.
   *
   * @param string $name
   *   Name of the image media.
   *
   * @return \Drupal\media\MediaInterface
   *   Media image instance.
   */
  protected function createMediaImage(string $name): MediaInterface {
    // Create file instance.
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/placeholder.png'), "public://placeholder_$name.png");
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'image',
      'name' => "Test image $name",
      'oe_media_image' => [
        'target_id' => (int) $file->id(),
        'alt' => "Alternative text $name",
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

}
