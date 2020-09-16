<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\Tests\oe_media\Traits\MediaCreationTrait;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;

/**
 * Tests organisation (oe_organisation) content type render.
 */
class ContentOrganisationRenderTest extends BrowserTestBase {

  use MediaCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'system',
    'path',
    'node',
    'address',
    'oe_media',
    'oe_theme_helper',
    'oe_theme_content_entity_contact',
    'oe_theme_content_organisation',
  ];

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

    // Give anonymous users permission to view entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the Organisation page renders correctly.
   */
  public function testOrganisationRendering(): void {
    $file = $this->createFile(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg');
    $media = $this->createMediaImage([
      'name' => 'test image',
      'file_id' => $file->id(),
      'alt' => 'Alt',
    ]);

    $contact = $this->getStorage('oe_contact')->create([
      'bundle' => 'oe_general',
      'name' => 'General contact',
      'oe_email' => 'example@test.com',
      'oe_phone' => '0123456789',
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => 'My address',
        'postal_code' => '1001',
      ],
    ]);

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_organisation',
      'title' => 'My node title',
      'oe_summary' => 'My introduction',
      'body' => 'My body text',
      'oe_organisation_acronym' => 'My acronym',
      'oe_organisation_logo' => [
        'target_id' => $media->id(),
      ],
      'oe_organisation_contact' => [$contact],
      'oe_teaser' => 'The teaser text',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $expected_values = [
      'title' => 'My node title',
      'description' => "My introduction\n",
      'meta' => 'My acronym',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Assert navigation part.
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $assert = new InPageNavigationAssert();
    $expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Description', 'href' => '#desciption'],
        ['label' => 'Contact', 'href' => '#contact'],
      ],
    ];
    $assert->assertPattern($expected_values, $navigation->getOuterHtml());

    $logo = $this->assertSession()->elementExists('css', '.ecl-col-lg-3 img.ecl-media-container__media');
    $this->assertContains('styles/oe_theme_ratio_3_2_medium/public/example_1.jpeg', $logo->getAttribute('src'));
    $this->assertEquals('Alt', $logo->getAttribute('alt'));

    // Assert content part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);

    // Assert header of first field group.
    $this->assertContentHeader($content_items[0], 'Description', 'description');

    // Assert values for first group.
    $body = $content_items[0]->findAll('css', '.ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals('My body text', $body[0]->getText());

    // Assert header of second field group.
    $this->assertContentHeader($content_items[1], 'Contact', 'contact');

    // Assert labels and values in second field group.
    $field_list = $content_items[1]->findAll('css', '.ecl-description-list--horizontal');
    $this->assertCount(1, $field_list);
    $labels = $field_list[0]->findAll('css', '.ecl-description-list__term');
    $this->assertCount(3, $labels);
    $labels_data = [
      'Phone number',
      'Address',
      'Email',
    ];
    foreach ($labels as $index => $element) {
      $this->assertEquals($labels_data[$index], $element->getText());
    }
    $values = $field_list[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(3, $values);
    $values_data = [
      '0123456789',
      'My address, 1001 Brussels, Belgium',
      'example@test.com',
    ];

    foreach ($values_data as $index => $value) {
      $this->assertEquals($value, $values[$index]->getText());
    }
  }

  /**
   * Asserts field group header.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Field group content.
   * @param string $title
   *   Expected title.
   * @param string $id
   *   Expected id.
   */
  protected function assertContentHeader(NodeElement $element, string $title, string $id): void {
    $header = $element->find('css', 'h2.ecl-u-type-heading-2');
    $this->assertEquals($title, $header->getText());
    $this->assertEquals($id, $header->getAttribute('id'));
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
