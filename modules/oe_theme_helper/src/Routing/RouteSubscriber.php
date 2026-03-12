<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alter UI Patterns Library overview page route.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::ALTER][] = ['onAlterRoutes', -200];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection): void {
    if ($route = $collection->get('sdc_component_library.component_list')) {
      $route->setPath('/components');
    }
  }

}
