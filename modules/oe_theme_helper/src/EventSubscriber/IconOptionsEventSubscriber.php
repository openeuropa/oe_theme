<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_paragraphs\Event\IconOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to alter the list of allowed paragraph icons.
 */
class IconOptionsEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      IconOptionsEvent::class => 'alterIconOptions',
    ];
  }

  /**
   * Alter the list of allowed paragraph icons.
   *
   * @param \Drupal\oe_paragraphs\Event\IconOptionsEvent $event
   *   The event.
   */
  public function alterIconOptions(IconOptionsEvent $event): void {
    $options = $event->getIconOptions();
    $options['log-in'] = $this->t('Log in');
    $options['logged-in'] = $this->t('Logged in');
    ksort($options);
    $event->setIconOptions($options);
  }

}
