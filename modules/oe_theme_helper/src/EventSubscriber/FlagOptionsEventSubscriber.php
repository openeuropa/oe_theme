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
    $options['EU Member states'] = $event->getFlagOptions();
    $options['Non-EU Member states'] = [
      'albania' => $this->t('Albania'),
      'armenia' => $this->t('Armenia'),
      'bosnia-and-herzegovina' => $this->t('Bosnia and Herzegovina'),
      'georgia' => $this->t('Georgia'),
      'iceland' => $this->t('Iceland'),
      'israel' => $this->t('Israel'),
      'liechtenstein' => $this->t('Liechtenstein'),
      'moldova' => $this->t('Moldova'),
      'montenegro' => $this->t('Montenegro'),
      'north-macedonia' => $this->t('North Macedonia'),
      'norway' => $this->t('Norway'),
      'serbia' => $this->t('Serbia'),
      'switzerland' => $this->t('Switzerland'),
      'turkey' => $this->t('Turkey'),
      'ukraine' => $this->t('Ukraine'),
      'united-kingdom' => $this->t('United Kingdom'),
    ];
    ksort($options);
    $event->setFlagOptions($options);
  }

}
