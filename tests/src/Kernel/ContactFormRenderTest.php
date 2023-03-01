<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\contact\Entity\ContactForm;
use Drupal\Tests\oe_contact_forms\Kernel\ContactFormTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that our contact forms renders with ecl markup.
 *
 * @group batch2
 */
class ContactFormRenderTest extends ContactFormTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
    'breakpoint',
    'responsive_image',
    'image',
    'oe_theme_contact_forms',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['system', 'image', 'responsive_image', 'contact']);
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);
  }

  /**
   * Tests that corporate contact form is rendered with the correct ECL markup.
   */
  public function testContactForm(): void {
    $contact_form = ContactForm::create(['id' => 'oe_contact_form']);
    $contact_form->setThirdPartySetting('oe_contact_forms', 'is_corporate_form', TRUE);
    $contact_form->setThirdPartySetting('oe_contact_forms', 'header', 'this is a test header');
    $privacy_url = 'http://example.net';
    $contact_form->setThirdPartySetting('oe_contact_forms', 'privacy_policy', $privacy_url);
    $optional_selected = ['oe_telephone' => 'oe_telephone'];
    $contact_form->setThirdPartySetting('oe_contact_forms', 'optional_fields', $optional_selected);
    $topics = [
      [
        'topic_name' => 'Topic name',
        'topic_email_address' => 'topic@emailaddress.com',
      ],
    ];
    $contact_form->setThirdPartySetting('oe_contact_forms', 'topics', $topics);
    $contact_form->save();

    /** @var \Drupal\contact\MessageInterface $contact_message */
    $message = $this->container->get('entity_type.manager')->getStorage('contact_message')->create([
      'contact_form' => $contact_form->id(),
      'name' => 'sender_name',
      'mail' => 'test@example.com',
      'subject' => 'subject',
      'message' => 'welcome_message',
      'oe_telephone' => '0123456',
      'oe_topic' => 'Topic name',
    ]);

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $form = $this->container->get('entity.form_builder')->getForm($message, 'corporate_default');
    $crawler = new Crawler($this->render($form));

    $actual = $crawler->filter('p.ecl-u-type-paragraph');
    $this->assertCount(1, $actual);
    $privacy_label = $crawler->filter('label.ecl-checkbox__label.form-required');
    $this->assertCount(1, $privacy_label);
    $this->assertEquals('I have read and agree with the personal data protection terms', $privacy_label->text());
    $privacy_link = $crawler->filter('.ecl-u-ml-2xs.ecl-link.ecl-link--default');
    $this->assertCount(1, $privacy_link);
    $this->assertEquals('personal data protection terms', $privacy_link->text());
    $actual = $crawler->filter('.ecl-u-mv-m');
    $this->assertCount(7, $actual);
    $telephone = $crawler->filter('.field--name-oe-telephone input');
    $this->assertEquals($telephone->attr('size'), 60);
    $this->assertStringContainsString('ecl-text-input', $telephone->attr('class'));

    /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $this->container->get('messenger');

    $full_view = $entity_type_manager->getViewBuilder('contact_message')->view($message, 'full');
    $messenger->addMessage($full_view);
    $messages = $messenger->messagesByType('status');
    $html = $this->render($messages);
    $crawler = new Crawler($html);

    $actual = $crawler->filter('dl.ecl-description-list--horizontal');
    $this->assertCount(1, $actual);
    $actual = $crawler->filter('dt.ecl-description-list__term');
    $this->assertCount(6, $actual);
    $actual = $crawler->filter('dd.ecl-description-list__definition');
    $this->assertCount(6, $actual);
  }

}
