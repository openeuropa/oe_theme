<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\media\Entity\Media;
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
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_teaser' => 'Test teaser text.',
      'oe_publication_type' => [
        'http://publications.europa.eu/resource/authority/resource-type/DIR_DEL',
      ],
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_author' => [
        'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Test Publication node',
      'meta' => "Delegated directive | 15 April 2020\n | Arab Common Market",
      'description' => 'Test teaser text.',
    ];
    $assert->assertPattern($expected_values, $html);

    // Add thumbnail.
    $media_image = $this->createMediaImage('publication_image');
    $node->set('oe_publication_thumbnail', $media_image)->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['image'] = [
      'src' => 'styles/oe_theme_publication_thumbnail/public/placeholder_publication_image.png',
      'alt' => '',
    ];
    $assert->assertPattern($expected_values, $html);

    // Add a second resource type.
    $node->set('oe_publication_type', [
      'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'http://publications.europa.eu/resource/authority/resource-type/AID_STATE',
    ]);
    // Add a second responsible department.
    $node->set('oe_author', [
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'http://publications.europa.eu/resource/authority/corporate-body/ACP-EU_JPA',
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['meta'] = "Abstract | State aid | 15 April 2020\n | Arab Common Market | ACP–EU Joint Parliamentary Assembly";
    $assert->assertPattern($expected_values, $html);
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

    $media = Media::create([
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
