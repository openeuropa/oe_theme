<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our News content type renders correctly.
 */
class ContentNewsRenderTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_news',
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
      ->save();
  }

  /**
   * Tests that the News page renders correctly.
   */
  public function testNewsRendering(): void {
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
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR',
      'oe_news_location' => 'http://publications.europa.eu/resource/authority/place/ARE_AUH',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

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
