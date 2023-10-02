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
    $button = $this->assertBalloonButtonOn('Table toolbar', 'Toggle simple mode off');
    $button->click();
    $assert_session->elementNotExists('css', '.ck-widget.table.table-simple', $editor);
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $this->assertEquals(
      '<table><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    $button = $this->assertBalloonButtonOff('Table toolbar', 'Toggle simple mode on');
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
    $button = $this->assertBalloonButtonOn('Table toolbar', 'Toggle zebra striping off');
    $button->click();
    $assert_session->elementNotExists('css', '.ck-widget.table.table-zebra-striped', $editor);
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $this->assertEquals(
      '<table><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    $button = $this->assertBalloonButtonOff('Table toolbar', 'Toggle zebra striping on');
    $button->click();
    $assert_session->elementsCount('css', '.ck-widget.table', 1, $editor);
    $assert_session->elementsCount('css', '.ck-widget.table.table-zebra-striped', 1, $editor);
    $this->assertEquals(
      '<table data-striped="true"><tbody><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );
  }

  /**
   * Tests the "table sort" plugin.
   */
  public function testTableSortPlugin(): void {
    $node = $this->drupalCreateNode([
      'type' => 'page',
      'status' => 1,
      'title' => 'Test page',
      'body' => [
        // A table with the 3 columns and 2 rows. The first row is set as header
        // and cell 1 and 3 are set as sortable.
        // In the body of the table, one cell is set (incorrectly) as sortable.
        'value' => '<table><thead><tr><th data-sortable="true">&nbsp;</th><th>&nbsp;</th><th data-sortable="true">&nbsp;</th></tr></thead><tbody><tr><td>&nbsp;</td><td>&nbsp;</td><td data-sortable="true">&nbsp;</td></tr></tbody></table>',
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
    $header_cells = $editor->findAll('css', '.ck-widget.table th');
    $this->assertCount(3, $header_cells);
    $this->assertTrue($header_cells[0]->hasClass('cell-sortable'));
    $this->assertFalse($header_cells[1]->hasClass('cell-sortable'));
    $this->assertTrue($header_cells[2]->hasClass('cell-sortable'));
    $assert_session->elementNotExists('css', '.ck-widget.table td.cell-sortable', $editor);
    // The td with the sortable attribute is ignored, but the attribute won't
    // be removed in this test scenario as there is no html filtering enabled.
    $this->assertEquals($node->get('body')->value, $this->getEditorDataAsHtmlString());

    // Check that the button gets the correct state on the header cells.
    $header_cells[0]->click();
    $this->assertBalloonButtonOn('Table toolbar', 'Toggle column sort off');
    $header_cells[1]->click();
    $this->assertBalloonButtonOff('Table toolbar', 'Toggle column sort on');
    // The button is disabled on anything that is not a header cell.
    $editor->find('css', 'td')->click();
    $this->assertBalloonButtonDisabled('Table toolbar', 'Toggle column sort on');

    // Enable sorting on the second header cell.
    $header_cells[1]->click();
    $this->assertVisibleBalloon('[aria-label="Table toolbar"]');
    $this->getBalloonButton('Toggle column sort on')->click();
    $this->assertTrue($header_cells[1]->hasClass('cell-sortable'));
    $this->assertEquals(
      '<table><thead><tr><th data-sortable="true">&nbsp;</th><th data-sortable="true">&nbsp;</th><th data-sortable="true">&nbsp;</th></tr></thead><tbody><tr><td>&nbsp;</td><td>&nbsp;</td><td data-sortable="true">&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    // Test turning off the sorting.
    $this->getBalloonButton('Toggle column sort off')->click();
    $this->assertFalse($header_cells[1]->hasClass('cell-sortable'));
    $this->assertEquals(
      '<table><thead><tr><th data-sortable="true">&nbsp;</th><th>&nbsp;</th><th data-sortable="true">&nbsp;</th></tr></thead><tbody><tr><td>&nbsp;</td><td>&nbsp;</td><td data-sortable="true">&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    // The button works on selections of multiple header cells.
    $header_cells[0]->click();
    // We use the keyboard to select the adjacent cell: shift + right arrow key.
    $header_cells[0]->keyDown(39, 'shift');
    $header_cells[0]->keyUp(39, 'shift');
    // The button is active only when all the selected header cells have the
    // sortable property set.
    $button = $this->assertBalloonButtonOff('Table toolbar', 'Toggle column sort on');
    // Clicking the button will toggle on/off the sorting on all selected cells.
    $button->click();
    $this->assertTrue($header_cells[0]->hasClass('cell-sortable'));
    $this->assertTrue($header_cells[1]->hasClass('cell-sortable'));
    // The third cell was already set to sortable.
    $this->assertTrue($header_cells[2]->hasClass('cell-sortable'));
    $this->assertEquals(
      '<table><thead><tr><th data-sortable="true">&nbsp;</th><th data-sortable="true">&nbsp;</th><th data-sortable="true">&nbsp;</th></tr></thead><tbody><tr><td>&nbsp;</td><td>&nbsp;</td><td data-sortable="true">&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );
    // Click again the button to disable the sorting.
    $this->assertBalloonButtonOn('Table toolbar', 'Toggle column sort off')->click();
    $this->assertBalloonButtonOff('Table toolbar', 'Toggle column sort on');
    $this->assertFalse($header_cells[0]->hasClass('cell-sortable'));
    $this->assertFalse($header_cells[1]->hasClass('cell-sortable'));
    // The third cell retained the sorting.
    $this->assertTrue($header_cells[2]->hasClass('cell-sortable'));
    $this->assertEquals(
      '<table><thead><tr><th>&nbsp;</th><th>&nbsp;</th><th data-sortable="true">&nbsp;</th></tr></thead><tbody><tr><td>&nbsp;</td><td>&nbsp;</td><td data-sortable="true">&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    // Test that the button is disabled when an element of the selection is not
    // a header cell. We start selecting a header cell with sortable set.
    $header_cells[2]->click();
    // Pressing the "down" arrow, select the below td cell.
    $header_cells[2]->keyDown(40, 'shift');
    $header_cells[2]->keyUp(40, 'shift');
    $this->assertBalloonButtonDisabled('Table toolbar', 'Toggle column sort on');

    // Test with a non-sortable header cell.
    $header_cells[1]->click();
    // Pressing the "down" arrow, select the below td cell.
    $header_cells[1]->keyDown(40, 'shift');
    $header_cells[1]->keyUp(40, 'shift');
    $this->assertBalloonButtonDisabled('Table toolbar', 'Toggle column sort on');

    // Test with a mix of sortable and not sortable header cells.
    $header_cells[1]->click();
    $header_cells[1]->keyDown(39, 'shift');
    $header_cells[1]->keyUp(39, 'shift');
    $header_cells[1]->keyDown(40, 'shift');
    $header_cells[1]->keyUp(40, 'shift');
    $this->assertBalloonButtonDisabled('Table toolbar', 'Toggle column sort on');

    // Test with a selection of multiple non-header cells.
    $cell = $editor->find('css', 'td:first-child');
    $cell->click();
    $cell->keyDown(39, 'shift');
    $cell->keyUp(39, 'shift');
    $this->assertBalloonButtonDisabled('Table toolbar', 'Toggle column sort on');

    // Test that removing the table row deletes all sortable attributes from
    // the table headers.
    // Select a cell in the header.
    $header_cells[0]->click();
    $this->assertVisibleBalloon('[aria-label="Table toolbar"]');
    $this->getBalloonButton('Row')->click();
    $this->getBalloonButton('Header row')->click();
    $assert_session->elementsCount('css', '.cell-sortable', 0, $editor);
    // The table cell with the incorrect data-sortable attribute retains it,
    // as for CKEditor that is a generic attribute, since the upcast didn't
    // apply to it.
    $this->assertEquals(
      '<table><tbody><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td data-sortable="true">&nbsp;</td></tr></tbody></table>',
      $this->getEditorDataAsHtmlString()
    );

    // Test that button is disabled for any header row after the first.
    $editor->find('css', 'tr:nth-of-type(2) td:first-of-type')->click();
    $this->assertVisibleBalloon('[aria-label="Table toolbar"]');
    $this->getBalloonButton('Row')->click();
    $this->getBalloonButton('Header row')->click();
    $assert_session->elementsCount('css', 'thead tr', 2, $editor);
    $editor->find('css', 'thead tr:nth-of-type(2) th:first-of-type')->click();
    $this->assertBalloonButtonDisabled('Table toolbar', 'Toggle column sort on');
    $editor->find('css', 'thead tr:nth-of-type(1) th:first-of-type')->click();
    $this->assertBalloonButtonOff('Table toolbar', 'Toggle column sort on');
  }

  /**
   * Tests that the table plugins declare the needed tags and attributes.
   */
  public function testFilterHtmlSupport(): void {
    $admin = $this->drupalCreateUser([
      'administer filters',
    ]);
    $this->drupalLogin($admin);
    $this->drupalGet('/admin/config/content/formats/manage/test_format');
    $assert_session = $this->assertSession();
    $assert_session->fieldExists('Limit allowed HTML tags and correct faulty HTML')->check();
    $assert_session->assertWaitOnAjaxRequest();
    $tags = $assert_session->fieldExists('Allowed HTML tags')->getValue();
    $this->assertStringContainsString('<table data-striped data-simple>', $tags);
    $this->assertStringContainsString('<th rowspan colspan data-sortable>', $tags);
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

  /**
   * Asserts that a balloon button is on.
   *
   * @param string $balloon_label
   *   The name of the balloon that contains the button.
   * @param string $button_label
   *   The button label.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The button.
   */
  protected function assertBalloonButtonOn(string $balloon_label, string $button_label): NodeElement {
    $this->assertVisibleBalloon(sprintf('[aria-label="%s"]', $balloon_label));
    $button = $this->getBalloonButton($button_label);
    $this->assertTrue($button->hasClass('ck-on'));
    $this->assertFalse($button->hasClass('ck-disabled'));

    return $button;
  }

  /**
   * Asserts that a balloon button is off.
   *
   * @param string $balloon_label
   *   The name of the balloon that contains the button.
   * @param string $button_label
   *   The button label.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The button.
   */
  protected function assertBalloonButtonOff(string $balloon_label, string $button_label): NodeElement {
    $this->assertVisibleBalloon(sprintf('[aria-label="%s"]', $balloon_label));
    $button = $this->getBalloonButton($button_label);
    $this->assertTrue($button->hasClass('ck-off'));
    $this->assertFalse($button->hasClass('ck-disabled'));

    return $button;
  }

  /**
   * Asserts that a balloon button is disabled.
   *
   * @param string $balloon_label
   *   The name of the balloon that contains the button.
   * @param string $button_label
   *   The button label.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The button.
   */
  protected function assertBalloonButtonDisabled(string $balloon_label, string $button_label): NodeElement {
    $this->assertVisibleBalloon(sprintf('[aria-label="%s"]', $balloon_label));
    $button = $this->getBalloonButton($button_label);
    $this->assertTrue($button->hasClass('ck-disabled'));

    return $button;
  }

}
