<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\node\NodeInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\oe_content_entity_venue\Entity\VenueInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\media\MediaInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

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

    // Give anonymous users permission to view entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->save();
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
    $figure = reset($figures);

    // Assert image tag.
    $image = $figure->find('css', 'img');
    $this->assertContains("placeholder_$name.png", $image->getAttribute('src'));
    $this->assertEquals("Alternative text $name", $image->getAttribute('alt'));

    // Assert caption.
    $caption = $figure->find('css', 'figcaption');
    $this->assertEquals("Caption $name", $caption->getText());
  }

  /**
   * Asserts rendering of Contact entity using default display view.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node entity.
   * @param string $contact_field_name
   *   Field name, reference to Contact entity.
   * @param array $settings
   *   Contact entity properties.
   */
  protected function assertContactEntityDefaultDisplay(NodeInterface $node, string $contact_field_name, array $settings = []):void {
    // Create Contact entity with required values only.
    $contact = $this->createContactEntity($settings);
    $name = $contact->getName();
    $node->set($contact_field_name, [$contact]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $rendered_element = $this->assertSession()->elementExists('css', '.oe_contact__default');

    // Assert that empty values don't exist.
    $this->assertSession()->elementNotExists('css', '.ecl-editor', $rendered_element);
    $this->assertSession()->elementNotExists('css', '.ecl-description-list', $rendered_element);
    $this->assertSession()->elementNotExists('css', 'figure.ecl-media-container', $rendered_element);
    $this->assertSession()->elementNotExists('css', '.ecl-u-border-top.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mt-s.ecl-u-pt-l.ecl-u-pb-l', $rendered_element);

    // Assert Contact title.
    $contact_sub_headers = $rendered_element->findAll('css', 'h3');
    $this->assertCount(1, $contact_sub_headers);
    $this->assertEquals($contact_sub_headers[0]->getText(), $name);

    // Add and assert body field.
    $contact->set('oe_body', 'Body text ' . $name);
    $contact->save();
    $this->drupalGet($node->toUrl());
    $body = $rendered_element->findAll('css', '.ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals("Body text $name", $body[0]->getText());

    // Add and assert Organisation field.
    $contact->set('oe_organisation', "Organisation $name");
    $contact->save();
    $this->drupalGet($node->toUrl());
    $field_list_assert = new FieldListAssert();
    $contact_expected_values = [];
    $contact_expected_values['items'][] = [
      'label' => 'Organisation',
      'body' => "Organisation $name",
    ];
    $contact_html = $rendered_element->getHtml();
    $field_list_assert->assertPattern($contact_expected_values, $contact_html);
    $field_list_assert->assertVariant('horizontal', $contact_html);

    // Add and assert Website field.
    $contact->set('oe_website', ['uri' => "http://www.example.com/website_$name"]);
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Website',
      'body' => "http://www.example.com/website_$name",
    ];
    $field_list_assert->assertPattern($contact_expected_values, $rendered_element->getHtml());

    // Add and assert Email field.
    $contact->set('oe_email', "$name@example.com");
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Email',
      'body' => "$name@example.com",
    ];
    $field_list_assert->assertPattern($contact_expected_values, $rendered_element->getHtml());

    // Add and assert Phone number field.
    $contact->set('oe_phone', "Phone number $name");
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Phone number',
      'body' => "Phone number $name",
    ];
    $field_list_assert->assertPattern($contact_expected_values, $rendered_element->getHtml());

    // Add and assert Mobile number field.
    $contact->set('oe_mobile', "Mobile number $name");
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Mobile number',
      'body' => "Mobile number $name",
    ];
    $field_list_assert->assertPattern($contact_expected_values, $rendered_element->getHtml());

    // Add and assert Fax number field.
    $contact->set('oe_fax', "Fax number $name");
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Fax number',
      'body' => "Fax number $name",
    ];
    $field_list_assert->assertPattern($contact_expected_values, $rendered_element->getHtml());

    // Add and assert Address field.
    $contact->set('oe_address', [
      'country_code' => 'BE',
      'locality' => 'Brussels',
      'address_line1' => "Address $name",
      'postal_code' => '1001',
    ]);
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Postal address',
      'body' => "Address $name, 1001 Brussels, Belgium",
    ];
    $field_list_assert->assertPattern($contact_expected_values, $rendered_element->getHtml());

    // Add and assert Office field.
    $contact->set('oe_office', "Office $name");
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Office',
      'body' => "Office $name",
    ];
    $field_list_assert->assertPattern($contact_expected_values, $rendered_element->getHtml());

    // Add and assert Social media links field.
    $contact->set('oe_social_media', [
      [
        'uri' => "http://www.example.com/social_media_$name",
        'title' => "Social media $name",
        'link_type' => 'facebook',
      ],
    ]);
    $contact->save();
    $this->drupalGet($node->toUrl());
    $contact_expected_values['items'][] = [
      'label' => 'Social media',
      'body' => html_entity_decode('&nbsp;') . "Social media $name",
    ];

    // Add and assert Image field.
    $media = $this->createMediaImage($name);
    $contact->set('oe_image', [
      [
        'target_id' => (int) $media->id(),
        'caption' => "Caption $name",
      ],
    ]);
    $contact->save();
    $this->drupalGet($node->toUrl());
    $this->assertFeaturedMediaField($rendered_element, $name);

    // Add and assert Press contacts field.
    $contact->set('oe_press_contact_url', ['uri' => "http://www.example.com/press_contact_$name"]);
    $contact->save();
    $this->drupalGet($node->toUrl());
    $press = $rendered_element->findAll('css', '.ecl-u-border-top.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mt-s.ecl-u-pt-l.ecl-u-pb-l');
    $press = reset($press);
    $press_link = $press->findAll('css', 'a');
    $this->assertCount(1, $press_link);
    $press_link = reset($press_link);
    $this->assertEquals("http://www.example.com/press_contact_$name", $press_link->getAttribute('href'));

    $press_label = $press_link->findAll('css', '.ecl-link__label');
    $this->assertCount(1, $press_label);
    $press_label = reset($press_label);
    $this->assertEquals('Press contacts', $press_label->getText());

    $press_icon = $press_link->findAll('css', '.ecl-icon.ecl-icon--s.ecl-icon--primary.ecl-link__icon');
    $this->assertCount(1, $press_icon);
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
  protected function createContactEntity(string $name, string $bundle = 'oe_general', int $status = CorporateEntityInterface::PUBLISHED): ContactInterface {
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
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $name
   *   Name of the entity.
   */
  protected function assertContactDefaultRender(NodeElement $element, string $name): void {
    $contact_name = $element->findAll('css', 'h3');
    $this->assertCount(1, $contact_name);
    $this->assertEquals($name, $contact_name[0]->getText());

    $contact_body = $element->findAll('css', '.ecl-editor');
    $this->assertCount(1, $contact_body);
    $this->assertEquals("Body text $name", $contact_body[0]->getText());

    $contacts_html = $element->getHtml();
    $field_list_assert = new FieldListAssert();
    $contact_expected_values = [
      'items' => [
        [
          'label' => 'Organisation',
          'body' => "Organisation $name",
        ], [
          'label' => 'Website',
          'body' => "http://www.example.com/website_$name",
        ], [
          'label' => 'Email',
          'body' => "$name@example.com",
        ], [
          'label' => 'Phone number',
          'body' => "Phone number $name",
        ], [
          'label' => 'Mobile number',
          'body' => "Mobile number $name",
        ], [
          'label' => 'Fax number',
          'body' => "Fax number $name",
        ], [
          'label' => 'Postal address',
          'body' => "Address $name, 1001 Brussels, Belgium",
        ], [
          'label' => 'Office',
          'body' => "Office $name",
        ], [
          'label' => 'Social media',
          'body' => html_entity_decode('&nbsp;') . 'Social media ' . $name,
        ],
      ],
    ];
    $field_list_assert->assertPattern($contact_expected_values, $contacts_html);
    $field_list_assert->assertVariant('horizontal', $contacts_html);

    // Assert Press contacts.
    $press = $element->find('css', '.ecl-u-border-top.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mt-s.ecl-u-pt-l.ecl-u-pb-l');
    $this->assertLinkIcon($press, 'Press contacts', "http://www.example.com/press_contact_$name");

    // Assert contacts Image.
    $this->assertFeaturedMediaField($element, $name);
  }

  /**
   * Creates Venue entity based on provided settings.
   *
   * @param string $name
   *   Entity name. Is used as a parameter for test data.
   * @param string $bundle
   *   Entity bundle.
   * @param int $status
   *   Entity status.
   *
   * @return \Drupal\oe_content_entity_venue\Entity\VenueInterface
   *   Venue entity instance.
   */
  protected function createVenueEntity(string $name, string $bundle = 'oe_default', int $status = CorporateEntityInterface::PUBLISHED): VenueInterface {
    $venue = $this->getStorage('oe_venue')->create([
      'bundle' => 'oe_default',
      'name' => $name,
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => "Address $name",
        'postal_code' => '1001',
      ],
      'oe_capacity' => "Capacity $name",
      'oe_room' => "Room $name",
      'status' => $status,
      'uid' => 0,
    ]);
    $venue->save();

    return $venue;
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
  protected function assertContentHeader(NodeElement $element, string $title, string $id = ''): void {
    $header = $element->find('css', 'h2.ecl-u-type-heading-2');
    $this->assertEquals($title, $header->getText());
    if (!empty($id)) {
      $this->assertEquals($id, $header->getAttribute('id'));
    }
  }

  /**
   * Asserts standalone link template with icon.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $title
   *   Link title.
   * @param string $href
   *   Link URL.
   * @param bool $is_external
   *   Defines whether it is extrernal link or internal.
   */
  protected function assertLinkIcon(NodeElement $element, string $title, string $href, bool $is_external = TRUE): void {
    $link = $element->findAll('css', 'a.ecl-link.ecl-link--standalone.ecl-link--icon.ecl-link--icon-after');
    $this->assertCount(1, $link);
    $this->assertEquals($href, $link[0]->getAttribute('href'));

    $label = $link[0]->findAll('css', '.ecl-link__label');
    $this->assertCount(1, $label);
    $this->assertEquals($title, $label[0]->getText());

    $svg_locator = 'svg.ecl-icon.ecl-icon--s.ecl-icon--primary.ecl-link__icon';
    $icon_type = 'ui--external';
    if (!$is_external) {
      $svg_locator = 'svg.ecl-icon.ecl-icon--s.ecl-icon--rotate-90.ecl-icon--primary.ecl-link__icon';
      $icon_type = 'ui--corner-arrow';
    }
    $svg = $link[0]->findAll('css', $svg_locator);
    $this->assertCount(1, $svg);
    $icon = $svg[0]->findAll('css', 'use');
    $this->assertCount(1, $icon);
    $this->assertContains($icon_type, $icon[0]->getAttribute('xlink:href'));
  }

}
