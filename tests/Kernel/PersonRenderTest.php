<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternAssertState;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests consultation rendering.
 */
class PersonRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'options',
    'field_group',
    'composite_reference',
    'oe_content_departments_field',
    'oe_content_person',
    'oe_content_organisation',
    'oe_content_organisation_reference',
    'oe_content_social_media_links_field',
    'oe_content_sub_entity_document_reference',
    'oe_theme_content_organisation',
    'oe_theme_content_person',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $entities = [
      'oe_contact',
      'oe_document_reference',
      'oe_person_job',
    ];
    foreach ($entities as $entity) {
      $this->installEntitySchema($entity);
    }

    $this->installConfig([
      'oe_content_departments_field',
      'oe_content_social_media_links_field',
      'oe_content_organisation_reference',
      'oe_content_organisation',
      'oe_content_person',
      'oe_theme_content_organisation',
      'oe_theme_content_person',
    ]);

    module_load_include('install', 'oe_content');
    oe_content_install();

    $this->setUpCurrentUser([], [], TRUE);
  }

  /**
   * Test a person being rendered as a teaser.
   */
  public function testTeaser(): void {
    // Create a Person node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $values = [
      'type' => 'oe_person',
      'oe_subject' => 'http://data.europa.eu/uxp/1005',
      'oe_person_type' => 'eu',
      'oe_person_first_name' => 'Mick',
      'oe_person_last_name' => 'Jagger',
      'oe_person_gender' => 'http://publications.europa.eu/resource/authority/human-sex/MALE',
      'oe_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'uid' => 0,
      'status' => 1,
    ];
    $node = Node::create($values);
    $node->save();

    // Check teaser with required fields only.
    $html = $this->getRenderedNode($node);
    $expected_values = [
      'title' => 'Mick Jagger',
      'meta' => NULL,
      'image' => [
        'src' => 'user_icon.svg',
        'alt' => '',
      ],
      'additional_information' => NULL,
    ];
    $assert = new ListItemAssert();
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_secondary', $html);

    // Assert Display name field.
    $node->set('oe_person_displayed_name', 'Jagger Mick')->save();
    $expected_values['title'] = 'Jagger Mick';
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert Portrait photo field.
    $portrait_media = $this->createMediaImage('person_portrait');
    $node->set('oe_person_photo', $portrait_media)->save();
    $expected_values['image']['src'] = 'placeholder_person_portrait.png';
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert Departments field.
    $node->set('oe_departments', 'http://publications.europa.eu/resource/authority/corporate-body/ABEC')->save();
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Department',
            'body' => 'Audit Board of the European Communities',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert multiple values in Departments field.
    $node->set('oe_departments', [
      'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
    ])->save();
    $expected_values['additional_information'] = [
      new PatternAssertState(new FieldListAssert(), [
        'items' => [
          [
            'label' => 'Departments',
            'body' => 'Audit Board of the European Communities | Arab Common Market',
          ],
        ],
      ]),
    ];
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));
  }

  /**
   * Renders node using provided view mode.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $node
   *   Node entity.
   * @param string $view_mode
   *   Node view mode.
   *
   * @return string
   *   Rendered content.
   */
  protected function getRenderedNode(ContentEntityInterface $node, $view_mode = 'teaser'): string {
    $build = $this->nodeViewBuilder->view($node, $view_mode);
    return $this->renderRoot($build);
  }

}
