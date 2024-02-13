<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests paragraphs forms.
 *
 * @group batch1
 */
class ParagraphsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'node',
    'oe_theme_helper',
    'oe_paragraphs_demo',
    'oe_content_timeline_field',
    'oe_paragraphs_media',
    'oe_multilingual',
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
  }

  /**
   * Test Accordion item paragraph form.
   */
  public function testAccordionItemParagraph(): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
    // Add an user.
    $user = $this->drupalCreateUser([], '', TRUE);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_demo_landing_page');
    $page = $this->getSession()->getPage();
    $page->pressButton('Add Accordion');

    // Assert the title and body fields of Accordion item paragraph are shown
    // but the icon field is not.
    $this->assertSession()->fieldExists('field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text][0][value]');
    $this->assertSession()->fieldExists('field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text_long][0][value]');
    $this->assertSession()->fieldNotExists('field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_icon]');

    $values = [
      'title[0][value]' => 'Test Accordion',
      'field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text][0][value]' => 'Accordion item title',
      'field_oe_demo_body[0][subform][field_oe_paragraphs][0][subform][field_oe_text_long][0][value]' => 'Accordion item body',
      'oe_content_content_owner[0][target_id]' => 'Directorate-General for Digital Services',
    ];
    $this->submitForm($values, 'Save');
    $this->drupalGet('/node/1');

    // Assert paragraph values are displayed.
    $this->assertSession()->pageTextContains('Accordion item title');
    $this->assertSession()->pageTextContains('Accordion item body');
  }

  /**
   * Tests the Media paragraph cache invalidation.
   */
  public function testMediaParagraphCaching(): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
    // Set image media translatable.
    $this->container->get('content_translation.manager')->setEnabled('media', 'image', TRUE);
    // Make the image field translatable.
    $field_config = $this->container->get('entity_type.manager')->getStorage('field_config')->load('media.image.oe_media_image');
    $field_config->set('translatable', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    // Create English file.
    $en_file = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_en.jpeg');
    $en_file->setPermanent();
    $en_file->save();

    // Create Bulgarian file.
    $bg_file = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1_bg.jpeg');
    $bg_file->setPermanent();
    $bg_file->save();

    // Create a media.
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $media_storage->create([
      'bundle' => 'image',
      'name' => 'test image en',
      'oe_media_image' => [
        'target_id' => $en_file->id(),
        'alt' => 'Alt en',
      ],
    ]);
    $media->save();
    // Translate the media to Bulgarian.
    $media_bg = $media->addTranslation('bg', [
      'name' => 'test image bg',
      'oe_media_image' => [
        'target_id' => $bg_file->id(),
        'alt' => 'Alt bg',
      ],
    ]);
    $media_bg->save();

    // Create a Media paragraph.
    $paragraph = $this->container
      ->get('entity_type.manager')
      ->getStorage('paragraph')->create([
        'type' => 'oe_av_media',
        'field_oe_media' => [
          'target_id' => $media->id(),
        ],
      ]);
    $paragraph->save();

    // Add Bulgarian translation.
    $paragraph->addTranslation('bg', $paragraph->toArray())->save();

    // Create a Demo page node referencing the paragraph.
    $node = $this->container->get('entity_type.manager')->getStorage('node')->create([
      'type' => 'oe_demo_landing_page',
      'title' => 'Demo Media paragraph',
      'field_oe_demo_body' => [$paragraph],
    ]);
    $node->save();
    // Add Bulgarian translation.
    $node->addTranslation('bg', $node->toArray())->save();

    // Assert the english translation.
    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementAttributeContains('css', 'figure.ecl-media-container__figure img.ecl-media-container__media', 'src', 'example_1_en.jpeg');
    // Assert the bulgarian translation.
    $this->drupalGet('/bg/node/' . $node->id(), ['external' => FALSE]);
    $this->assertSession()->elementAttributeContains('css', 'figure.ecl-media-container__figure img.ecl-media-container__media', 'src', 'example_1_bg.jpeg');

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();
    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', 'figure.ecl-media-container__figure');

    // Create a remote video and add it to the paragraph.
    $media = $media_storage->create([
      'bundle' => 'remote_video',
      'oe_media_oembed_video' => [
        'value' => 'https://www.youtube.com/watch?v=7gngmXxdmyI',
      ],
    ]);
    $media->save();
    $paragraph->set('field_oe_media', ['target_id' => $media->id()]);
    $paragraph->save();

    $this->getSession()->reload();
    $partial_iframe_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=7gngmXxdmyI',
      ],
    ])->toString();
    $this->assertSession()->elementAttributeContains('css', 'figure.ecl-media-container__figure div.ecl-media-container__media iframe', 'src', $partial_iframe_url);
  }

}
