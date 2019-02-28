<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched after the page header has been assembled.
 *
 * It allows alterations of the elements that make up the page header.
 */
class PageHeaderAlterEvent extends Event {

  /**
   * The event name used when dispatching this event.
   */
  const EVENT_NAME = 'page_header.alter';

  /**
   * The element.
   *
   * @var array
   */
  protected $element;

  /**
   * Returns the element.
   *
   * @return array
   *   The element.
   */
  public function getElement(): array {
    return $this->element;
  }

  /**
   * Sets the element.
   *
   * @param array $element
   *   The element.
   */
  public function setElement(array $element): void {
    $this->element = $element;
  }

}
