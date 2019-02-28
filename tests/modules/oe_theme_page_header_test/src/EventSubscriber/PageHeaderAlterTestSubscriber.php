<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_page_header_test\EventSubscriber;

use Drupal\oe_theme_helper\Event\PageHeaderAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber that changes the title of the page header.
 */
class PageHeaderAlterTestSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[PageHeaderAlterEvent::EVENT_NAME][] = ['onPageHeaderBuild', 0];
    return $events;
  }

  /**
   * Performs the alterations.
   *
   * @param \Drupal\oe_theme_helper\Event\PageHeaderAlterEvent $event
   *   The event object.
   */
  public function onPageHeaderBuild(PageHeaderAlterEvent $event) {
    $element = $event->getElement();
    if ($element['#title'] == 'Alter it') {
      $element['#title'] = 'Altered title';
    }
    $event->setElement($element);
  }

}
