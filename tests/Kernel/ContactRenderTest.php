<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests contact rendering.
 */
class ContactRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content_entity',
    'oe_theme_content_entity_contact',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('oe_contact');
    $this->installConfig([
      'oe_theme_content_entity_contact',
    ]);

    $this->contactStorage = $this->container->get('entity_type.manager')->getStorage('oe_contact');
    $this->contactViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('oe_contact');
  }

  /**
   * Test a contact being rendered using full view.
   */
  public function testFullView(): void {
    // Create Contact entity with required values only.
    $contact = $this->createContact();
    $name = $contact->getName();

    $build = $this->contactViewBuilder->view($contact, 'full');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);

    // Assert that empty values don't exist.
    $this->assertEmpty($crawler->filter('.ecl-editor'));
    $this->assertEmpty($crawler->filter('.ecl-description-list'));
    $this->assertEmpty($crawler->filter('figure.ecl-media-container'));
    $this->assertEmpty($crawler->filter('.ecl-u-border-top.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mt-s.ecl-u-pt-l.ecl-u-pb-l'));
    $this->assertEmpty($crawler->filter('.ecl-row.ecl-u-mv-xl .ecl-col-md-6'));
    $this->assertEmpty($crawler->filter('.ecl-row.ecl-u-mv-xl .ecl-col-md-5'));

    // Assert Contact title.
    $contact_sub_headers = $crawler->filter('h3');
    $this->assertCount(1, $contact_sub_headers);
    $this->assertEquals($name, trim($contact_sub_headers->text()));

    // Add and assert body field.
    $contact->set('oe_body', 'Body text ' . $name)->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);
    $rendered_body = $crawler->filter('.ecl-col-12 .ecl-editor');
    $this->assertCount(1, $rendered_body);
    $this->assertEquals("Body text $name", trim($rendered_body->text()));

    // Add and assert Organisation field.
    $contact->set('oe_organisation', "Organisation $name")->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $html = $this->renderRoot($build);
    $field_list_assert = new FieldListAssert();
    $expected_values = [];
    $expected_values['items'][] = [
      'label' => 'Organisation',
      'body' => "Organisation $name",
    ];
    $field_list_assert->assertPattern($expected_values, $html);
    $field_list_assert->assertVariant('horizontal', $html);

    // Add and assert Website field.
    $contact->set('oe_website', ['uri' => "http://www.example.com/website_$name"])->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Website',
      'body' => "http://www.example.com/website_$name",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Email field.
    $contact->set('oe_email', "$name@example.com")->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Email',
      'body' => "$name@example.com",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Phone number field.
    $contact->set('oe_phone', "Phone number $name")->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Phone number',
      'body' => "Phone number $name",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Mobile number field.
    $contact->set('oe_mobile', "Mobile number $name")->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Mobile number',
      'body' => "Mobile number $name",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Fax number field.
    $contact->set('oe_fax', "Fax number $name")->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Fax number',
      'body' => "Fax number $name",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Address field.
    $contact->set('oe_address', [
      'country_code' => 'BE',
      'locality' => 'Brussels',
      'address_line1' => "Address $name",
      'postal_code' => '1001',
    ])->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Postal address',
      'body' => "Address $name, 1001 Brussels, Belgium",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Office field.
    $contact->set('oe_office', "Office $name")->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Office',
      'body' => "Office $name",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Social media links field.
    $contact->set('oe_social_media', [
      [
        'uri' => "http://www.example.com/social_media_$name",
        'title' => "Social media $name",
        'link_type' => 'facebook',
      ],
    ])->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $expected_values['items'][] = [
      'label' => 'Social media',
      'body' => html_entity_decode('&nbsp;') . "Social media $name",
    ];
    $field_list_assert->assertPattern($expected_values, $this->renderRoot($build));

    // Add and assert Image field.
    $media = $this->createMediaImage($name);
    $contact->set('oe_image', [
      [
        'target_id' => (int) $media->id(),
        'caption' => "Caption $name",
      ],
    ])->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $html = $this->renderRoot($build);
    $this->assertFeaturedMediaField($html, $name);
    $crawler = new Crawler($html);

    // Ensure that wrapper for body field has been changed.
    $rendered_body = $crawler->filter('.ecl-row.ecl-u-mv-xl .ecl-col-md-6 .ecl-editor');
    $this->assertCount(1, $rendered_body);
    $this->assertEquals("Body text $name", trim($rendered_body->text()));

    // Add and assert Press contacts field.
    $contact->set('oe_press_contact_url', ['uri' => "http://www.example.com/press_contact_$name"])->save();
    $build = $this->contactViewBuilder->view($contact, 'full');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);
    $press = $crawler->filter('.ecl-u-border-top.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mt-s.ecl-u-pt-l.ecl-u-pb-l');
    $press_link = $press->filter('a');
    $this->assertCount(1, $press_link);
    $this->assertEquals("http://www.example.com/press_contact_$name", $press_link->attr('href'));

    $press_label = $press_link->filter('.ecl-link__label');
    $this->assertCount(1, $press_label);
    $this->assertEquals('Press contacts', trim($press_label->text()));

    $press_icon = $press_link->filter('.ecl-icon.ecl-icon--s.ecl-icon--primary.ecl-link__icon');
    $this->assertCount(1, $press_icon);
  }

  /**
   * Creates Contact entity based on provided settings.
   *
   * @param array $settings
   *   Entity configuration.
   *
   * @return \Drupal\oe_content_entity_contact\Entity\ContactInterface
   *   Contact entity instance.
   */
  protected function createContact(array $settings = []): ContactInterface {
    // Define default values.
    $default_settings = [
      'bundle' => 'oe_general',
      'name' => $this->randomMachineName(),
      'status' => CorporateEntityInterface::PUBLISHED,
      'uid' => 0,
    ];
    $settings += $default_settings;
    $contact = $this->contactStorage->create($settings);
    $contact->save();

    return $contact;
  }

  /**
   * Asserts featured media field rendering.
   *
   * @param string $html
   *   Rendered element.
   * @param string $name
   *   Name of the image media.
   */
  protected function assertFeaturedMediaField(string $html, string $name): void {
    $crawler = new Crawler($html);
    $figure = $crawler->filter('.ecl-row.ecl-u-mv-xl .ecl-col-md-5 figure.ecl-media-container');
    $this->assertCount(1, $figure);

    // Assert image tag.
    $image = $figure->filter('img');
    $this->assertContains("placeholder_$name.png", $image->attr('src'));
    $this->assertEquals("Alternative text $name", $image->attr('alt'));

    // Assert caption.
    $caption = $figure->filter('figcaption');
    $this->assertEquals("Caption $name", trim($caption->text()));
  }

}
