<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alter UI Patterns Library overview page route.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection): void {
    if ($route = $collection->get('ui_patterns.patterns.overview')) {
      $route->setDefault('_title', 'Components');
      $route->setPath('/components');
    }
    if ($route = $collection->get('ui_patterns.patterns.single')) {
      $route->setPath('/components/{name}');
    }
  }

}
