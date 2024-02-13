<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_paragraphs\Event\FlagOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to alter the list of allowed flag icons.
 */
class FlagOptionsEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FlagOptionsEvent::class => 'alterFlagOptions',
    ];
  }

  /**
   * Alter the list of allowed flag icons.
   *
   * @param \Drupal\oe_paragraphs\Event\FlagOptionsEvent $event
   *   The event.
   */
  public function alterFlagOptions(FlagOptionsEvent $event): void {
    $options = $event->getFlagOptions();
    $options['albania'] = $this->t('Albania');
    $options['bosnia-and-herzegovina'] = $this->t('Bosnia and Herzegovina');
    $options['georgia'] = $this->t('Georgia');
    $options['iceland'] = $this->t('Iceland');
    $options['moldova'] = $this->t('Moldova');
    $options['montenegro'] = $this->t('Montenegro');
    $options['north-macedonia'] = $this->t('North Macedonia');
    $options['norway'] = $this->t('Norway');
    $options['serbia'] = $this->t('Serbia');
    $options['switzerland'] = $this->t('Switzerland');
    $options['turkey'] = $this->t('Turkey');
    $options['ukraine'] = $this->t('Ukraine');
    ksort($options);
    $event->setFlagOptions($options);
  }

}
