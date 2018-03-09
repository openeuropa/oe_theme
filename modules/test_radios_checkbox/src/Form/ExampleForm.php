<?php

namespace Drupal\test_radios_checkbox\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Test radios checkbox form.
 */
class ExampleForm extends FormBase {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a ExampleForm object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger instance.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'test_radios_checkbox_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	  $form = [];
	  $options = [];

	  $list = $this->generator->wordList();
	  foreach ($list as $item) {
		  $options[$item] = $item;
	  }

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    ];
	  $form['copy'] = array(
		  '#type' => 'checkbox',
		  '#title' => $this
			  ->t('Send me a copy'),
	  );
	  $form['checkbox'] = [
		  '#type' => 'checkbox',
		  '#title' => $this->t('Checkbox'),
		  '#value' => 1,
		  '#default_value' => 1,
		  '#description' => $this->generator->sentence(),
	  ];
	  $form['checkboxes'] = [
		  '#type' => 'checkboxes',
		  '#title' => $this->t('Checkboxes'),
		  '#options' => $options,
		  '#description' => $this->generator->sentence(),
	  ];
	  $form['radios'] = [
		  '#type' => 'radios',
		  '#title' => $this->t('Radios'),
		  '#options' => $options,
		  '#description' => $this->generator->sentence(),
	  ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (Unicode::strlen($form_state->getValue('message')) < 10) {
      $form_state->setErrorByName('name', $this->t('Message should be at least 10 characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->loggerFactory->get('test_radios_checkbox')->debug(
      $form_state->getValue('message')
    );
    drupal_set_message($this->t('The message has been sent.'));
    $form_state->setRedirect('system.admin');
  }

}
