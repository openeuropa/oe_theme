<?php

declare(strict_types=1);

namespace Drupal\oe_theme_js_test\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A form that simulates adding dropdowns through AJAX.
 */
class MultiSelectTestForm implements FormInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_theme_js_multiselect_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['multi_select'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Select element'),
      '#options' => [
        '1' => $this->t('One'),
        '2' => [
          '2.1' => $this->t('Two point one'),
          '2.2' => $this->t('Two point two'),
        ],
        '3' => $this->t('Three'),
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

}
