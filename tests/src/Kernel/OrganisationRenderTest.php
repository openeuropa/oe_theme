<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternAssertState;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the organisation rendering.
 *
 * @group batch2
 */
class OrganisationRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'field_group',
    'entity_reference_revisions',
    'link',
    'options',
    'image',
    'inline_entity_form',
    'description_list_field',
    'oe_content_featured_media_field',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_organisation',
    'oe_content_organisation_reference',
    'composite_reference',
    'oe_theme_content_entity_contact',
    'oe_theme_content_organisation',
    'description_list_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('oe_contact');
    $this->installConfig([
      'oe_content_featured_media_field',
      'oe_content_entity_contact',
      'oe_content_organisation',
      'oe_content_organisation_reference',
      'oe_theme_content_entity_contact',
      'oe_theme_content_organisation',
    ]);

    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);

    // Set current user to UID 1, so that by default we can access everything.
    $account = User::load(1);
    $this->setCurrentUser($account);
  }

  /**
   * Test a organisation being rendered as a teaser.
   */
  public function testOrganisationTeaser(): void {
    $logo_media = $this->createMediaImage('organisation_logo');
    $first_contact = $this->createContactEntity('first_contact', 'oe_general');

    $node = Node::create([
      'type' => 'oe_organisation',
      'title' => 'Organisation name',
      'oe_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'oe_teaser' => 'The teaser text',
      'oe_organisation_acronym' => 'Acronym',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => 'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'oe_organisation_logo' => [
        [
          'target_id' => $logo_media->id(),
        ],
      ],
      'oe_organisation_contact' => [
        [
          'target_id' => $first_contact->id(),
          'target_revision_id' => $first_contact->getRevisionId(),
        ],
      ],
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Organisation name',
      'url' => '/en/node/1',
      'description' => 'The teaser text',
      'meta' => 'International organisation | Acronym',
      'image' => [
        'src' => 'placeholder_organisation_logo.png',
        'alt' => '',
      ],
      'date' => NULL,
      'additional_information' => [
        new PatternAssertState(new FieldListAssert(), [
          'items' => [
            [
              'label' => 'Website',
              'body' => 'http://www.example.com/website_first_contact',
            ],
            [
              'label' => 'Email',
              'body' => 'first_contact@example.com',
            ],
            [
              'label' => 'Phone number',
              'body' => 'Phone number first_contact',
            ],
            [
              'label' => 'Address',
              'body' => 'Address first_contact, 1001 Brussels, Belgium',
            ],
          ],
        ]),
      ],
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_secondary', $html);

    // Test short title fallback.
    $node->set('oe_content_short_title', 'Organisation short title')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['title'] = 'Organisation short title';
    $assert->assertPattern($expected_values, $html);

    // Create another contact and add it to the node.
    $second_contact = $this->createContactEntity('second_contact', 'oe_general');
    $node->set('oe_organisation_contact', [$first_contact, $second_contact]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);
    $first_contact_render = $crawler->filter('article .ecl-content-item__additional_information.ecl-u-mb-s div.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-mb-m.ecl-u-pb-m');
    $this->assertCount(1, $first_contact_render);

    $field_assert = new FieldListAssert();
    $second_contact_expected_values = [
      'items' => [
        [
          'label' => 'Website',
          'body' => 'http://www.example.com/website_second_contact',
        ],
        [
          'label' => 'Email',
          'body' => 'second_contact@example.com',
        ],
        [
          'label' => 'Phone number',
          'body' => 'Phone number second_contact',
        ],
        [
          'label' => 'Address',
          'body' => 'Address second_contact, 1001 Brussels, Belgium',
        ],
      ],
    ];
    $second_contact_render = $crawler->filter('article div.ecl-content-item__additional_information.ecl-u-mb-s div:nth-child(2)');
    $field_assert->assertPattern($second_contact_expected_values, $second_contact_render->html());

    // Change organisation type to non eu.
    $node->set('oe_organisation_contact', NULL);
    $node->set('oe_organisation_org_type', 'non_eu');
    $node->set('oe_organisation_non_eu_org_type', 'http://data.europa.eu/uxp/5432');
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['additional_information'] = NULL;
    $expected_values['meta'] = 'embassy | Acronym';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_secondary', $html);

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

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['image'] = [
      'src' => 'files/styles/oe_theme_medium_no_crop/public/media_avportal_thumbnails/' . $file->getFilename(),
      'alt' => '',
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_secondary', $html);
  }

}
