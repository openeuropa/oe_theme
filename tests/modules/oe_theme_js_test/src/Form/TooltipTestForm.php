<?php

declare(strict_types=1);

namespace Drupal\oe_theme_js_test\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides an example form with tooltip variants.
 */
class TooltipTestForm implements FormInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_theme_js_tooltip_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $tooltip = [
      'data-ecl-tooltip' => 'tooltip',
    ];

    $tooltip_inverted = [
      'data-ecl-tooltip-inverted' => 'inverted tooltip',
    ];

    $form['info'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Each field has normal + inverted tooltip version.'),
    ];

    $form['textfield_normal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Normal tooltip (Textfield)'),
      '#attributes' => $tooltip,
    ];

    $form['textfield_inverted'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inverted tooltip (Textfield)'),
      '#attributes' => $tooltip_inverted,
    ];

    $form['textarea_normal'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Normal tooltip (Textarea)'),
      '#attributes' => $tooltip,
      '#rows' => 2,
    ];

    $form['textarea_inverted'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Inverted tooltip (Textarea)'),
      '#attributes' => $tooltip_inverted,
      '#rows' => 2,
    ];

    $form['email_normal'] = [
      '#type' => 'email',
      '#title' => $this->t('Normal tooltip (Email)'),
      '#attributes' => $tooltip,
    ];

    $form['email_inverted'] = [
      '#type' => 'email',
      '#title' => $this->t('Inverted tooltip (Email)'),
      '#attributes' => $tooltip_inverted,
    ];

    $form['number_normal'] = [
      '#type' => 'number',
      '#title' => $this->t('Normal tooltip (Number)'),
      '#attributes' => $tooltip,
    ];

    $form['number_inverted'] = [
      '#type' => 'number',
      '#title' => $this->t('Inverted tooltip (Number)'),
      '#attributes' => $tooltip_inverted,
    ];

    $form['select_normal'] = [
      '#type' => 'select',
      '#title' => $this->t('Normal tooltip (Select)'),
      '#options' => [
        'one' => $this->t('One'),
        'two' => $this->t('Two'),
      ],
      '#attributes' => $tooltip,
    ];

    $form['select_inverted'] = [
      '#type' => 'select',
      '#title' => $this->t('Inverted tooltip (Select)'),
      '#options' => [
        'one' => $this->t('One'),
        'two' => $this->t('Two'),
      ],
      '#attributes' => $tooltip_inverted,
    ];

    $form['checkbox_normal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Normal tooltip (Checkbox)'),
      '#attributes' => $tooltip,
    ];

    $form['checkbox_inverted'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inverted tooltip (Checkbox)'),
      '#attributes' => $tooltip_inverted,
    ];

    $form['radios_normal'] = [
      '#type' => 'radios',
      '#title' => $this->t('Normal tooltip (Radios)'),
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
      '#attributes' => $tooltip,
    ];

    $form['radios_inverted'] = [
      '#type' => 'radios',
      '#title' => $this->t('Inverted tooltip (Radios)'),
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
      '#attributes' => $tooltip_inverted,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit_normal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit (Normal tooltip)'),
      '#attributes' => $tooltip,
    ];

    $form['actions']['submit_inverted'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit (Inverted tooltip)'),
      '#attributes' => $tooltip_inverted,
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
