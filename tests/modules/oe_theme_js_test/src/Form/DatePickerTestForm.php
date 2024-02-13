<?php

declare(strict_types=1);

namespace Drupal\oe_theme_js_test\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form that simulates adding datepicker through ecl library.
 */
class DatePickerTestForm extends FormBase {

  use StringTranslationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a DatePickerTestForm.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_theme_js_datepicker_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['test_datepicker_one'] = [
      '#type' => 'date',
      '#title' => $this->t('Test date picker one'),
      '#description' => $this->t('Date field one.'),
      '#required' => TRUE,
    ];

    $form['test_datepicker_two'] = [
      '#type' => 'date',
      '#title' => $this->t('Test date picker two'),
      '#description' => $this->t('Date field two.'),
      '#default_value' => DrupalDateTime::createFromFormat('Y-m-d', '2020-05-10'),
      '#required' => FALSE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
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
    $values[] = $form_state->getValue('test_datepicker_one');
    $values[] = $form_state->getValue('test_datepicker_two');
    foreach ($values as $key => $value) {
      if (!$value) {
        continue;
      }
      $date = DrupalDateTime::createFromFormat('Y-m-d', $value);
      $this->messenger->addStatus($this->t('Date @key is @date', [
        '@key' => $key,
        '@date' => $date->format('j F Y'),
      ]));
    }
  }

}
