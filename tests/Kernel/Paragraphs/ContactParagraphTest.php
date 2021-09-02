<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\Contact;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of the Contact paragraph.
 *
 * @group batch1
 */
class ContactParagraphTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field_group',
    'address',
    'composite_reference',
    'inline_entity_form',
    'media',
    'oe_media',
    'file_link',
    'oe_content_entity_contact',
    'oe_content_featured_media_field',
    'oe_content_entity',
    'oe_theme_content_entity_contact',
    'oe_paragraphs_contact',
    'oe_theme_paragraphs_contact',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('media');
    $this->installEntitySchema('oe_contact');
    $this->installConfig([
      'composite_reference',
      'oe_content_entity_contact',
      'address',
      'media',
      'oe_media',
      'oe_content_entity',
      'oe_theme_content_entity_contact',
      'oe_paragraphs_contact',
    ]);

    module_load_include('install', 'media');
    media_install();
    $this->container->get('module_handler')->loadInclude('oe_theme_paragraphs_contact', 'install');
    oe_theme_paragraphs_contact_install(FALSE);

    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Test contact paragraph rendering.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testContact(): void {
    // Create a media entity for contact.
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();
    $media = $this->container->get('entity_type.manager')->getStorage('media')->create([
      'bundle' => 'image',
      'name' => "Test image",
      'oe_media_image' => [
        'target_id' => (int) $file->id(),
        'alt' => "Alternative text",
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();
    // Create general contact.
    $general_contact = Contact::create([
      'bundle' => 'oe_general',
      'name' => 'General contact',
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => 'Address of General contact',
        'postal_code' => '1001',
      ],
      'oe_body' => 'General contact body text',
      'oe_email' => 'general@example.com',
      'oe_organisation' => 'General contact Organisation',
      'oe_social_media' => [
        [
          'uri' => 'http://www.example.com/facebook',
          'title' => 'Facebook',
          'link_type' => 'facebook',
        ],
      ],
      'oe_image' => [
        [
          'target_id' => (int) $media->id(),
          'caption' => "General contact media caption",
        ],
      ],
      'status' => CorporateEntityInterface::PUBLISHED,
    ]);
    $general_contact->save();
    // Create press contact.
    $press_contact = Contact::create([
      'bundle' => 'oe_press',
      'name' => 'Press contact',
      'oe_body' => 'Press contact body text',
      'status' => CorporateEntityInterface::PUBLISHED,
    ]);
    $press_contact->save();
    // Create contact paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_contact',
      'field_oe_contacts' => [$general_contact, $press_contact],
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Assert paragraph heading markup is not rendered if no title is set.
    $this->assertCount(0, $crawler->filter('h2.ecl-u-type-heading-2'));
    // Assert rendering of the first contact.
    $this->assertEquals('General contact', trim($crawler->filter('div.ecl-row.ecl-u-mv-xl:nth-child(1) h3.ecl-u-type-heading-3.ecl-u-mt-none div')->html()));
    $this->assertEquals('<p>General contact body text</p>', trim($crawler->filter('div.ecl-col-m-6 div.ecl-u-mb-l:nth-child(1) div.ecl')->html()));
    $this->assertEquals('General contact Organisation', trim($crawler->filter('dl.ecl-description-list.ecl-description-list--horizontal:nth-child(2) dd.ecl-description-list__definition:nth-child(2) div')->html()));
    $this->assertEquals('general@example.com', trim($crawler->filter('dl.ecl-description-list.ecl-description-list--horizontal:nth-child(2) dd.ecl-description-list__definition:nth-child(4) div a')->html()));
    $this->assertEquals('Address of General contact, 1001 Brussels, Belgium', trim($crawler->filter('dl.ecl-description-list.ecl-description-list--horizontal:nth-child(2) dd.ecl-description-list__definition:nth-child(6) div span')->html()));
    $this->assertEquals('Facebook', trim($crawler->filter('dl.ecl-description-list.ecl-description-list--horizontal:nth-child(2) dd.ecl-description-list__definition:nth-child(8) div div div a span')->html()));
    $this->assertContains('example_1.jpeg', $crawler->filter('div.ecl-col-m-5 figure.ecl-media-container img')->attr('src'));
    // Assert rendering of the second contact.
    $this->assertEquals('Press contact', trim($crawler->filter('div.ecl-row.ecl-u-mv-xl:nth-child(2) h3.ecl-u-type-heading-3.ecl-u-mt-none div')->html()));
    $this->assertEquals('<p>Press contact body text</p>', trim($crawler->filter('div.ecl-col-12:nth-child(2) div.ecl-u-mb-l div.ecl')->html()));

    // Set paragraph title and assert rendering is updated.
    $paragraph->set('field_oe_title', 'Contact paragraph Test')->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    $this->assertEquals('Contact paragraph Test', trim($crawler->filter('h2.ecl-u-type-heading-2')->html()));
  }

}
