<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests call for tenders rendering.
 *
 * @group batch2
 */
class PublicationRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * Test a publication being rendered as a teaser.
   */
  public function testTeaser(): void {
    /** @var \Drupal\node\Entity\Node $node */
    $node = Node::create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_teaser' => 'Test teaser text.',
      'oe_publication_type' => [
        'http://publications.europa.eu/resource/authority/resource-type/DIR_DEL',
      ],
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_author' => [
        'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Test Publication node',
      'meta' => "Delegated directive | 15 April 2020\n | Arab Common Market",
      'description' => 'Test teaser text.',
    ];
    $assert->assertPattern($expected_values, $html);

    // Test short title fallback.
    $node->set('oe_content_short_title', 'Publication short title')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['title'] = 'Publication short title';
    $assert->assertPattern($expected_values, $html);

    // Add thumbnail.
    $media_image = $this->createMediaImage('publication_image');
    $node->set('oe_publication_thumbnail', $media_image)->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['image'] = [
      'src' => 'styles/oe_theme_publication_thumbnail/public/placeholder_publication_image.png',
      'alt' => '',
    ];
    $assert->assertPattern($expected_values, $html);

    // Add a second resource type.
    $node->set('oe_publication_type', [
      'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'http://publications.europa.eu/resource/authority/resource-type/AID_STATE',
    ]);
    // Add a second responsible department.
    $node->set('oe_author', [
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'http://publications.europa.eu/resource/authority/corporate-body/ACP-EU_JPA',
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['meta'] = "Abstract, State aid | 15 April 2020\n | Arab Common Market, ACP–EU Joint Parliamentary Assembly";
    $assert->assertPattern($expected_values, $html);
  }

}
