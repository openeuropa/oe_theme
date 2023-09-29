<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Test the table plugins for CKEditor 5.
 *
 * @group batch3
 *
 * @group oe_theme_helper
 */
class Ckeditor5TableTest extends WebDriverTestBase {

  use CKEditor5TestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'ckeditor5',
    'node',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user to be used in the test.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $user;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    FilterFormat::create([
      'format' => 'test_format',
      'name' => 'Test format',
      'weight' => 1,
      'roles' => [RoleInterface::AUTHENTICATED_ID],
      'filters' => [
        'filter_ecl_table' => [
          'status' => 1,
        ],
      ],
    ])->save();

    Editor::create([
      'editor' => 'ckeditor5',
      'format' => 'test_format',
      'settings' => [
        'toolbar' => [
          'items' => [
            'insertTable',
          ],
        ],
      ],
      'image_upload' => [
        'status' => FALSE,
      ],
    ])->save();
    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair(
        Editor::load('test_format'),
        FilterFormat::load('test_format')
      ))
    ));

    $bundle = NodeType::create([
      'type' => 'page',
      'name' => 'Page',
    ]);
    $bundle->save();
    node_add_body_field($bundle);

    $this->user = $this->drupalCreateUser([
      'access content',
      'edit any page content',
      'use text format test_format',
    ]);
  }

  /**
   * Tests the "table simple" plugin.
   */
  public function testTableSimplePlugin(): void {
    $node = $this->drupalCreateNode([
      'type' => 'page',
      'status' => 1,
      'title' => 'Test page',
      'body' => [
        'value' => '<table data-simple="true"><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
        'format' => 'test_format',
      ],
    ]);

    $this->drupalLogin($this->user);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->waitForEditor();
    $editor = $this->getEditor();
    $assert_session = $this->assertSession();
    // Check that the HTML has been upcasted correctly.
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $assert_session->elementsCount('css', '.ck-widget.table.table-simple', 1, $editor);
    $this->assertEquals($node->get('body')->value, $this->getEditorDataAsHtmlString());

    // Test the dedicated button behaviour.
    $this->click('.ck-widget.table');
    $this->assertVisibleBalloon('[aria-label="Table toolbar"]');
    $button = $this->getBalloonButton('Toggle simple mode off');
    $this->assertTrue($button->hasClass('ck-on'));
    $button->click();
    $assert_session->elementNotExists('css', '.ck-widget.table.table-simple', $editor);
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $this->assertEquals(
      '<table><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    $button = $this->getBalloonButton('Toggle simple mode on');
    $this->assertTrue($button->hasClass('ck-off'));
    $button->click();
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $assert_session->elementsCount('css', '.ck-widget.table.table-simple', 1, $editor);
    $this->assertEquals(
      '<table data-simple="true"><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );
  }

  /**
   * Tests the "table zebra striping" plugin.
   */
  public function testTableZebraStripingPlugin(): void {
    $node = $this->drupalCreateNode([
      'type' => 'page',
      'status' => 1,
      'title' => 'Test page',
      'body' => [
        'value' => '<table data-striped="true"><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
        'format' => 'test_format',
      ],
    ]);

    $this->drupalLogin($this->user);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->waitForEditor();
    $editor = $this->getEditor();
    $assert_session = $this->assertSession();
    // Check that the HTML has been upcasted correctly.
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $assert_session->elementsCount('css', '.ck-widget.table.table-zebra-striped', 1, $editor);
    $this->assertEquals($node->get('body')->value, $this->getEditorDataAsHtmlString());

    // Test the dedicated button behaviour.
    $this->click('.ck-widget.table');
    $this->assertVisibleBalloon('[aria-label="Table toolbar"]');
    $button = $this->getBalloonButton('Toggle zebra striping off');
    $this->assertTrue($button->hasClass('ck-on'));
    $button->click();
    $assert_session->elementNotExists('css', '.ck-widget.table.table-zebra-striped', $editor);
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $this->assertEquals(
      '<table><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    $button = $this->getBalloonButton('Toggle zebra striping on');
    $this->assertTrue($button->hasClass('ck-off'));
    $button->click();
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $assert_session->elementsCount('css', '.ck-widget.table.table-zebra-striped', 1, $editor);
    $this->assertEquals(
      '<table data-striped="true"><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );
  }

  /**
   * Returns the CKEditor 5 editor element that contains the content.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The editor element.
   */
  protected function getEditor(): NodeElement {
    return $this->assertSession()->elementExists('css', '.ck-editor .ck-content');
  }

}
