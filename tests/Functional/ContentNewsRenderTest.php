<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our News content type renders correctly.
 */
class ContentNewsRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_news',
    'oe_theme_content_entity_contact',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    \Drupal::service('theme_installer')->install(['oe_theme']);
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'oe_theme')->save();
    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the News page renders correctly.
   */
  public function testNewsRendering(): void {
    // Create general contact.
    $general_contact = [
      $this->createContactEntity('general_contact', 'oe_general', CorporateEntityInterface::PUBLISHED),
    ];
    // Create a News node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_teaser' => 'News teaser',
      'oe_summary' => 'News summary',
      'body' => 'Body',
      'oe_reference_code' => 'News reference',
      'oe_publication_date' => [
        'value' => '2020-09-18',
      ],
      'oe_news_contacts' => $general_contact,
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR',
      'oe_news_location' => 'http://publications.europa.eu/resource/authority/place/ARE_AUH',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert news contacts.
    $contacts = $this->assertSession()->elementExists('css', 'div#news-contacts');

    $contact_headers = $contacts->findAll('css', 'h2');
    $this->assertEquals('Contacts', $contact_headers[0]->getText());

    $contact_name = $contacts->findAll('css', 'h3');
    $this->assertCount(1, $contact_name);
    $contact_name = reset($contact_name);
    $this->assertEquals('general_contact', $contact_name->getText());

    $contact_body = $contacts->findAll('css', '.ecl-editor');
    $this->assertCount(1, $contact_body);
    $contact_body = reset($contact_body);
    $this->assertEquals('Body text general_contact', $contact_body->getText());

    $contacts_html = $contacts->getHtml();
    $field_list_assert = new FieldListAssert();
    $contact_expected_values = [
      'items' => [
        [
          'label' => 'Organisation',
          'body' => 'Organisation general_contact',
        ], [
          'label' => 'Website',
          'body' => 'http://www.example.com/website_general_contact',
        ], [
          'label' => 'Email',
          'body' => 'general_contact@example.com',
        ], [
          'label' => 'Phone number',
          'body' => 'Phone number general_contact',
        ], [
          'label' => 'Mobile number',
          'body' => 'Mobile number general_contact',
        ], [
          'label' => 'Fax number',
          'body' => 'Fax number general_contact',
        ], [
          'label' => 'Postal address',
          'body' => 'Address general_contact, 1001 Brussels, Belgium',
        ], [
          'label' => 'Office',
          'body' => 'Office general_contact',
        ], [
          'label' => 'Social media',
          'body' => html_entity_decode('&nbsp;') . 'Social media general_contact',
        ],
      ],
    ];
    $field_list_assert->assertPattern($contact_expected_values, $contacts_html);
    $field_list_assert->assertVariant('horizontal', $contacts_html);

    // Assert Press contacts.
    $press = $contacts->findAll('css', '.ecl-u-border-top.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mt-s.ecl-u-pt-l.ecl-u-pb-l');
    $press_link = $press[0]->findAll('css', 'a');
    $this->assertCount(1, $press_link);
    $press_link = reset($press_link);
    $this->assertEquals('http://www.example.com/press_contact_general_contact', $press_link->getAttribute('href'));

    $press_label = $press_link->findAll('css', '.ecl-link__label');
    $this->assertCount(1, $press_label);
    $press_label = reset($press_label);
    $this->assertEquals('Press contacts', $press_label->getText());

    $press_icon = $press_link->findAll('css', '.ecl-icon.ecl-icon--s.ecl-icon--primary.ecl-link__icon');
    $this->assertCount(1, $press_icon);

    // Assert contacts Image.
    $this->assertFeaturedMediaField($contacts, 'general_contact');

    // Assert page header - metadata.
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__title', 'Test news node');
    $this->assertSession()->elementTextContains('css', '.ecl-page-header-core .ecl-page-header-core__description', 'News summary');

    // Assert news details.
    $details = $this->assertSession()->elementExists('css', 'div#news-details');
    $field_list_assert = new FieldListAssert();
    $details_expected_values = [
      'items' => [
        [
          'label' => 'Reference',
          'body' => 'News reference',
        ],
        [
          'label' => 'Publication date',
          'body' => '18 September 2020',
        ],
        [
          'label' => 'Author',
          'body' => 'African Court of Justice and Human Rights',
        ],
        [
          'label' => 'Location',
          'body' => 'Abu Dhabi',
        ],
      ],
    ];
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);

    // Assert Author field label.
    $node->set('oe_author', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ACP_CDE'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $details_expected_values['items'][2]['label'] = 'Authors';
    $details_expected_values['items'][2]['body'] = 'African Court of Justice and Human Rights | Centre for the Development of Enterprise';
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
  }

}
