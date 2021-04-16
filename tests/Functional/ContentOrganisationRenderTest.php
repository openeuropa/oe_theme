<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\media\MediaInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\media\Entity\Media;

/**
 * Tests organisation (oe_organisation) content type render.
 */
class ContentOrganisationRenderTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'system',
    'path',
    'node',
    'address',
    'oe_theme_helper',
    'oe_theme_content_entity_contact',
    'oe_theme_content_organisation',
    'page_header_metadata_test',
    'media_avportal_mock',
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
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'image',
      'name' => 'test image',
      'oe_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    $general_contact = $this->createContactEntity('general_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_organisation',
      'title' => 'My node title',
      'oe_summary' => 'My introduction',
      'oe_organisation_acronym' => 'My acronym',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => 'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'oe_organisation_logo' => [
        'target_id' => $media->id(),
      ],
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
      'description' => 'My introduction',
      'meta' => 'International organisation | My acronym',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Change organisation type to non eu.
    $node->set('oe_organisation_org_type', 'non_eu');
    $node->set('oe_organisation_non_eu_org_type', 'http://data.europa.eu/uxp/5432');
    $node->save();
    $this->drupalGet($node->toUrl());

    $expected_values['meta'] = 'embassy | My acronym';
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    $logo = $this->assertSession()->elementExists('css', '.ecl-col-lg-3 img.ecl-media-container__media');
    $this->assertContains('styles/oe_theme_medium_no_crop/public/example_1.jpeg', $logo->getAttribute('src'));
    $this->assertEquals('Alt', $logo->getAttribute('alt'));

    // Add body text and contact values.
    $node->set('body', 'My body text');
    $node->set('oe_organisation_contact', [$general_contact]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert navigation part.
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $assert = new InPageNavigationAssert();
    $expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Contact', 'href' => '#contact'],
      ],
    ];
    $assert->assertPattern($expected_values, $navigation->getOuterHtml());

    // Change logo to use av portal image.
    $media = Media::create([
      'bundle' => 'av_portal_photo',
      'oe_media_avportal_photo' => 'P-038924/00-15',
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();
    $file = $media->get('thumbnail')->entity;

    $node->set('oe_organisation_logo', [
      [
        'target_id' => (int) $media->id(),
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $logo = $this->assertSession()->elementExists('css', '.ecl-col-lg-3 img.ecl-media-container__media');
    $this->assertContains('files/styles/oe_theme_medium_no_crop/public/media_avportal_thumbnails/' . $file->getFilename(), $logo->getAttribute('src'));

    // Add overview values.
    $node->set('oe_organisation_overview', [
      [
        'term' => 'Overview Term 1',
        'description' => 'Overview Description 1',
      ],
      [
        'term' => 'Overview Term 2',
        'description' => 'Overview Description 2',
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert content part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
    $content_items = $content->findAll('xpath', '/div');

    // Assert header of the first field group.
    $this->assertContentHeader($content_items[0], 'Overview', 'overview');
    // Assert values of the first group.
    $overview = $content_items[0]->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal');
    $this->assertCount(1, $overview);
    $overview_terms = $overview[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(2, $overview_terms);
    $this->assertEquals('Overview Term 1', $overview_terms[0]->getText());
    $this->assertEquals('Overview Term 2', $overview_terms[1]->getText());
    $overview_descriptions = $overview[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(2, $overview_descriptions);
    $this->assertEquals('Overview Description 1', $overview_descriptions[0]->getText());
    $this->assertEquals('Overview Description 2', $overview_descriptions[1]->getText());

    // Assert body field label is not displayed anymore.
    $this->assertSession()->pageTextNotContains('Description');
    // Assert values of the second group.
    $body = $content_items[1]->findAll('css', '.ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals('My body text', $body[0]->getText());

    // Assert header of the third field group.
    $this->assertContentHeader($content_items[2], 'Contact', 'contact');

    // Assert Organisation's contacts.
    $contact_headers = $content_items[2]->findAll('css', 'h2');
    $this->assertEquals($contact_headers[0]->getText(), 'Contact');
    $this->assertContactRendering($content_items[2], 'general_contact');
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
   * Creates Contact entity.
   *
   * @param string $name
   *   Entity name. Is used as a parameter for test data.
   * @param string $bundle
   *   Entity bundle.
   * @param int $status
   *   Entity status.
   *
   * @return \Drupal\oe_content_entity_contact\Entity\ContactInterface
   *   Contact entity.
   */
  protected function createContactEntity(string $name, string $bundle, int $status): ContactInterface {
    // Create image for contact.
    $media = $this->createMediaImage($name);

    $contact = $this->getStorage('oe_contact')->create([
      'bundle' => $bundle,
      'name' => $name,
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => "Address $name",
        'postal_code' => '1001',
      ],
      'oe_body' => "Body text $name",
      'oe_email' => "$name@example.com",
      'oe_fax' => "Fax number $name",
      'oe_mobile' => "Mobile number $name",
      'oe_office' => "Office $name",
      'oe_organisation' => "Organisation $name",
      'oe_phone' => "Phone number $name",
      'oe_press_contact_url' => ['uri' => "http://www.example.com/press_contact_$name"],
      'oe_social_media' => [
        [
          'uri' => "http://www.example.com/social_media_$name",
          'title' => "Social media $name",
          'link_type' => 'facebook',
        ],
      ],
      'oe_website' => ['uri' => "http://www.example.com/website_$name"],
      'oe_image' => [
        [
          'target_id' => (int) $media->id(),
          'caption' => "Caption $name",
        ],
      ],
      'status' => $status,
    ]);

    return $contact;
  }

  /**
   * Asserts rendering of Contact entity.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_element
   *   Rendered element.
   * @param string $name
   *   Name of the entity.
   */
  protected function assertContactRendering(NodeElement $rendered_element, string $name): void {
    $contact_sub_headers = $rendered_element->findAll('css', 'h3');
    $this->assertCount(1, $contact_sub_headers);
    $this->assertEquals($contact_sub_headers[0]->getText(), 'general_contact');

    // Body field.
    $body = $rendered_element->findAll('css', '.ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals("Body text $name", $body[0]->getText());

    // Assert list of fields in field_list pattern.
    $description_lists = $rendered_element->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal');
    $this->assertCount(1, $description_lists);

    // Assert labels in list of fields.
    $description_list_labels = $description_lists[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(9, $description_list_labels);
    $labels = [
      'Organisation',
      'Website',
      'Email',
      'Phone number',
      'Mobile number',
      'Fax number',
      'Postal address',
      'Office',
      'Social media',
    ];
    foreach ($labels as $key => $label) {
      $this->assertEquals($label, $description_list_labels[$key]->getText());
    }

    // Assert values in list of fields.
    $values = $description_lists[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(9, $values);
    $this->assertEquals("Organisation $name", $values[0]->getText());

    $values[1]->hasLink("http://www.example.com/website_$name");
    $values[2]->hasLink("$name@example.com");
    $this->assertEquals("Phone number $name", $values[3]->getText());
    $this->assertEquals("Mobile number $name", $values[4]->getText());
    $this->assertEquals("Fax number $name", $values[5]->getText());
    $this->assertEquals("Address $name, 1001 Brussels, Belgium", $values[6]->getText());
    $this->assertEquals("Office $name", $values[7]->getText());

    // Assert social media link.
    $social_media_links = $values[8]->findAll('css', '.ecl-link');
    $this->assertCount(1, $social_media_links);
    $social_media_link_label = $social_media_links[0]->find('css', '.ecl-link__label');
    $this->assertEqual("Social media $name", $social_media_link_label->getText());
    $this->assertContains("http://www.example.com/social_media_$name", $social_media_links[0]->getAttribute('href'));
    $social_media_icon = $social_media_links[0]->find('css', 'use');
    $this->assertContains('facebook', $social_media_icon->getAttribute('xlink:href'));

    // Assert image.
    $this->assertFeaturedMediaField($rendered_element, $name);
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
   * Asserts featured media field rendering.
   *
   * @param \Behat\Mink\Element\NodeElement $rendered_element
   *   Rendered element.
   * @param string $name
   *   Name of the image media.
   */
  protected function assertFeaturedMediaField(NodeElement $rendered_element, string $name): void {
    $figures = $rendered_element->findAll('css', 'figure.ecl-media-container');
    $this->assertCount(1, $figures);

    // Assert image tag.
    $image = $figures[0]->find('css', 'img');
    $this->assertContains("placeholder_$name.png", $image->getAttribute('src'));
    $this->assertEquals("Alternative text $name", $image->getAttribute('alt'));

    // Assert caption.
    $caption = $figures[0]->find('css', 'figcaption');
    $this->assertEquals("Caption $name", $caption->getText());
  }

}
