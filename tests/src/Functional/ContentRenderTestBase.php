<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\media\MediaInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\oe_content_entity_venue\Entity\VenueInterface;
use Drupal\oe_content_event_event_programme\Entity\ProgrammeItemInterface;
use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;
use Drupal\oe_content_sub_entity_document_reference\Entity\DocumentReference;
use Drupal\Tests\BrowserTestBase;
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
  protected static $modules = [
    'content_translation',
    'config',
    'datetime_testing',
    'block',
    'system',
    'path',
    'field_group',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

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

    $this->config('oe_theme_helper.internal_domains')->set('internal_domain', '/(^|^[^:]+:\/\/|[^\.]+\.)europa\.eu/m')->save();
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
    $file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.module')->getPath('oe_media') . '/tests/fixtures/sample.pdf'), "public://sample_$name.pdf");
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
   * @param string $language
   *   Document language.
   * @param string $meta
   *   Size and format of the file.
   * @param string $link
   *   Link to the file.
   * @param string $button_label
   *   Text on the button.
   */
  protected function assertMediaDocumentDefaultRender(NodeElement $element, string $name, string $language, string $meta, string $link, string $button_label): void {
    // Assert documents file.
    $file_wrapper = $element->find('css', 'div.ecl-file');
    $file_row = $file_wrapper->find('css', '.ecl-file .ecl-file__container');
    $file_footer = $file_wrapper->find('css', '.ecl-file .ecl-file__footer');
    $file_title = $file_row->find('css', '.ecl-file__title');
    $this->assertStringContainsString($name, $file_title->getText());
    $file_info_language = $file_footer->find('css', ' div.ecl-file__language');
    $this->assertStringContainsString($language, $file_info_language->getText());
    $file_info_properties = $file_footer->find('css', 'div.ecl-file__meta');
    $this->assertStringContainsString("($meta)", $file_info_properties->getText());
    $file_download_link = $file_footer->find('css', '.ecl-file__download');
    $this->assertStringContainsString($link, $file_download_link->getAttribute('href'));
    $this->assertStringContainsString($button_label, $file_download_link->getText());
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
    $file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/placeholder.png'), "public://placeholder_$name.png");
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
    $figures = $rendered_element->findAll('css', 'figure.ecl-media-container__figure');
    $this->assertCount(1, $figures);
    $figure = reset($figures);

    // Assert image tag.
    $image = $figure->find('css', 'img');
    $this->assertStringContainsString("placeholder_$name.png", $image->getAttribute('src'));
    $this->assertEquals("Alternative text $name", $image->getAttribute('alt'));

    // Assert caption.
    $caption = $figure->find('css', 'figcaption');
    $this->assertEquals("Caption $name", $caption->getText());
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
      'oe_link' => [
        'uri' => "http://www.example.com/link_$name",
        'title' => "Link title $name",
      ],
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
    $contact->save();
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

    $contact_body = $element->findAll('css', '.ecl');
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
          'body' => 'Social media ' . $name,
        ],
      ],
    ];
    $field_list_assert->assertPattern($contact_expected_values, $contacts_html);
    $field_list_assert->assertVariant('horizontal', $contacts_html);

    // Assert Press contacts field.
    $links_wrapper = $element->findAll('css', 'div.ecl-u-border-top.ecl-u-border-color-neutral-40.ecl-u-mt-s div.ecl-u-border-bottom.ecl-u-border-color-neutral-40.ecl-u-pt-l.ecl-u-pb-l');
    $this->assertCount(2, $links_wrapper);
    $this->assertLinkIcon($links_wrapper[0], 'Press contacts', "http://www.example.com/press_contact_$name");

    // Assert Link field.
    $this->assertLinkIcon($links_wrapper[1], "Link title $name", "http://www.example.com/link_$name");

    // Assert contacts Image.
    $this->assertFeaturedMediaField($element, $name);
  }

  /**
   * Asserts rendering of Contact entity using Details view mode.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $name
   *   Name of the Contact entity.
   */
  protected function assertContactDetailsRender(NodeElement $element, string $name): void {
    $field_list_assert = new FieldListAssert();
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Name',
          'body' => $name,
        ], [
          'label' => 'Email',
          'body' => "$name@example.com",
        ], [
          'label' => 'Phone number',
          'body' => "Phone number $name",
        ], [
          'label' => 'Address',
          'body' => "Address $name, 1001 Brussels, Belgium",
        ],
      ],
    ];
    $content = $this->assertSession()->elementExists('css', 'dl.ecl-description-list', $element);
    $field_list_html = $content->getOuterHtml();
    $field_list_assert->assertPattern($field_list_expected_values, $field_list_html);
    $field_list_assert->assertVariant('horizontal', $field_list_html);

    // Assert "Social media links" links.
    $social_links_assert = new SocialMediaLinksAssert();
    $social_links_expected_values = [
      'title' => 'Social media',
      'links' => [
        [
          'service' => 'facebook',
          'label' => "Social media $name",
          'url' => "http://www.example.com/social_media_$name",
        ],
      ],
    ];
    $social_links_content = $this->assertSession()->elementExists('css', '.ecl-u-mt-l', $element);
    $social_links_html = $social_links_content->getHtml();
    $social_links_assert->assertPattern($social_links_expected_values, $social_links_html);
    $social_links_assert->assertVariant('horizontal', $social_links_html);
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
        'locality' => '<Brussels>',
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
   * Creates Programme item entity based on provided settings.
   *
   * @param string $name
   *   Entity name. Is used as a parameter for test data.
   * @param string $bundle
   *   Entity bundle.
   * @param int $status
   *   Entity status.
   *
   * @return \Drupal\oe_content_event_event_programme\Entity\ProgrammeItemInterface
   *   Venue entity instance.
   */
  protected function createProgrammeItemEntity(string $name, string $bundle = 'oe_default', int $status = CorporateEntityInterface::PUBLISHED): ProgrammeItemInterface {
    $start_datetime = new DrupalDateTime('2020-02-17 14:00:00', DateTimeItemInterface::STORAGE_TIMEZONE);
    $end_datetime = (clone $start_datetime)->modify('+ 10 days');

    $programme_item = $this->getStorage('oe_event_programme')->create([
      'bundle' => 'oe_default',
      'name' => $name,
      'oe_event_programme_dates' => [
        'value' => $start_datetime->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'end_value' => $end_datetime->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'timezone' => 'UTC',
      ],
      'oe_description' => "Description $name",
      'status' => $status,
      'uid' => 0,
    ]);
    $programme_item->save();
    return $programme_item;
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
   *   Defines whether it is external link or internal.
   * @param string $icon_size
   *   Icon size.
   */
  protected function assertLinkIcon(NodeElement $element, string $title, string $href, bool $is_external = TRUE, string $icon_size = 's'): void {
    $link = $element->findAll('css', 'a.ecl-link.ecl-link--standalone.ecl-link--icon');
    $this->assertCount(1, $link);
    $this->assertEquals($href, $link[0]->getAttribute('href'));

    $label = $link[0]->findAll('css', '.ecl-link__label');
    $this->assertCount(1, $label);
    $this->assertEquals($title, $label[0]->getText());

    $svg_locator = 'svg.ecl-icon.ecl-icon--' . $icon_size . '.ecl-icon--primary.ecl-link__icon';
    $icon_type = 'external';
    if (!$is_external) {
      $svg_locator = 'svg.ecl-icon.ecl-icon--' . $icon_size . '.ecl-icon--rotate-90.ecl-icon--primary.ecl-link__icon';
      $icon_type = 'corner-arrow';
    }
    $svg = $link[0]->findAll('css', $svg_locator);
    $this->assertCount(1, $svg);
    $icon = $svg[0]->findAll('css', 'use');
    $this->assertCount(1, $icon);
    $this->assertStringContainsString($icon_type, $icon[0]->getAttribute('xlink:href'));
  }

  /**
   * Creates Publication Document reference entity.
   *
   * @param string $title
   *   Publication title.
   * @param int $status
   *   Entity status.
   *
   * @return \Drupal\oe_content_sub_entity_document_reference\Entity\DocumentReference
   *   Document reference publication entity.
   */
  protected function createPublicationDocumentReferenceEntity(string $title, int $status = SubEntityInterface::PUBLISHED): DocumentReference {
    $document = $this->createMediaDocument('document');
    /** @var \Drupal\node\Entity\Node $publication */
    $publication = $this->getStorage('node')->create([
      'type' => 'oe_publication',
      'title' => $title,
      'oe_teaser' => 'Teaser text',
      'oe_publication_type' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'oe_documents' => $document,
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'status' => 1,
    ]);
    $publication->save();
    $publication_reference = $this->getStorage('oe_document_reference')->create([
      'type' => 'oe_publication',
      'oe_publication' => $publication,
      'status' => $status,
    ]);
    $publication_reference->save();
    return $publication_reference;
  }

  /**
   * Creates Document Document reference entity.
   *
   * @param string $name
   *   Entity name. Is used as a parameter for test data.
   * @param int $status
   *   Entity status.
   *
   * @return \Drupal\oe_content_sub_entity_document_reference\Entity\DocumentReference
   *   Document reference document entity.
   */
  protected function createDocumentDocumentReferenceEntity(string $name, int $status = SubEntityInterface::PUBLISHED): DocumentReference {
    $document = $this->createMediaDocument($name);
    $document_reference = $this->getStorage('oe_document_reference')->create([
      'type' => 'oe_document',
      'oe_document' => $document,
      'status' => $status,
    ]);
    $document_reference->save();
    return $document_reference;
  }

  /**
   * Freeze time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $static_time
   *   Time to freeze.
   */
  protected function freezeTime(DrupalDateTime $static_time): void {
    /** @var \Drupal\datetime_testing\TestTimeInterface $time */
    $time = \Drupal::time();
    $time->freezeTime();
    $time->setTime($static_time->getTimestamp());
  }

}
