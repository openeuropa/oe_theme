<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_js_test\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * A form that simulates adding dropdowns through AJAX.
 */
class AjaxDropdownsTestForm implements FormInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_theme_js_test_ajax_rebuild_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $max_delta = $form_state->get('max_delta') ?? 0;

    $form['dropdowns'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'dropdown-container',
      ],
    ];

    for ($i = 0; $i <= $max_delta; $i++) {
      $form['dropdowns'][$i] = [
        '#type' => 'container',
        '#attributes' => [
          // We need to space between the patterns and the submit button.
          'class' => ['ecl-u-pb-4xl'],
        ],
      ];
      $form['dropdowns'][$i]['pattern'] = [
        '#type' => 'pattern',
        '#id' => 'dropdown',
        '#fields' => [
          'button_label' => $this->t('Dropdown @count', ['@count' => $i]),
          'links' => [
            [
              'url' => Url::fromRoute('<front>'),
              'label' => $this->t('Child link @count', ['@count' => $i]),
            ],
          ],
        ],
      ];
    }

    $form['add_more'] = [
      '#type' => 'submit',
      '#name' => 'add_more',
      '#value' => t('Add another'),
      '#submit' => [[get_class($this), 'addMoreSubmit']],
      '#ajax' => [
        'callback' => [get_class($this), 'addMoreAjax'],
        'wrapper' => 'dropdown-container',
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * Submit handler for the add more submit button.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    $max_delta = $form_state->get('max_delta');
    $form_state->set('max_delta', ++$max_delta);
    $form_state->setRebuild();
  }

  /**
   * Ajax callback that returns the latest added element.
   */
  public static function addMoreAjax(array $form, FormStateInterface $form_state) {
    $max_delta = $form_state->get('max_delta');

    $response = new AjaxResponse();
    $html = \Drupal::service('renderer')->renderRoot($form['dropdowns'][$max_delta]);
    $response->addCommand(new AppendCommand('#dropdown-container', $html));
    $response->setAttachments($form['dropdowns'][$max_delta]['#attached']);

    return $response;
  }

}
