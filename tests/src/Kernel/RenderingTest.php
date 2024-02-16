<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Tests that rendering of elements follows the theme implementation.
 *
 * @group batch2
 */
class RenderingTest extends AbstractKernelTestBase implements FormInterface {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_theme_patterns_render_test',
    'text',
    'filter',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('filter');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_theme_rendering_test_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $structure
   *   The structure of the form, read from the fixtures files.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $structure = NULL): array {
    $form['test'] = $structure;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Recurse through all the form elements and check if they have a property
    // "#set_validation_error". If they have, set a generic error on the
    // element.
    $add_errors = function (array $element) use (&$add_errors, $form_state): void {
      if (!empty($element['#set_validation_error'])) {
        // When the title is not present for a form element, fallback to its
        // path in the form.
        $label = !empty($element['#title']) ? $element['#title'] : implode('][', $element['#array_parents']);
        $form_state->setError($element, t('Validation error on @label', ['@label' => $label]));
      }

      foreach (Element::children($element) as $key) {
        // Recursively call this closure on all the children elements.
        $add_errors($element[$key]);
      }
    };

    $add_errors($form['test']);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Test rendering of elements.
   *
   * @param array $structure
   *   A render array.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider renderingDataProvider
   */
  public function testRendering(array $structure, array $assertions): void {
    // @todo Remove when support for 10.1.x is dropped.
    if (!empty($structure['core_version'])) {
      if (!$this->shouldBeTested($structure['core_version'])) {
        $this->markTestSkipped();
      }
      unset($structure['core_version']);
    }

    // Wrap all the test structure inside a form. This will allow proper
    // processing of form elements and invocation of form alter hooks.
    // Even if the elements being tested are not form related, the form can
    // host them without causing any issues.
    $form_state = new FormState();
    $form_state->addBuildInfo('args', [$structure]);
    $form_state->setProgrammed();

    $form = $this->container->get('form_builder')->buildForm($this, $form_state);
    $this->assertRendering($this->renderRoot($form), $assertions);
  }

  /**
   * Data provider for rendering tests.
   *
   * The actual data is read from fixtures stored in a YAML configuration.
   *
   * @return array
   *   A set of dump data for testing.
   */
  public function renderingDataProvider(): array {
    return $this->getFixtureContent('rendering.yml');
  }

  /**
   * Check if the Drupal core version falls within the specified minor range.
   *
   * @todo Remove when support for 10.1.x is dropped.
   *
   * @param string $core_version
   *   The minor version required by the test, e.g., '10.1'.
   *
   * @return bool
   *   Returns true if the Drupal version is within the minor version range.
   */
  protected function shouldBeTested(string $core_version): bool {
    $current_version_parts = explode('.', \Drupal::VERSION);
    $current_minor_version = $current_version_parts[0] . '.' . $current_version_parts[1];
    return $current_minor_version === $core_version;
  }

}
