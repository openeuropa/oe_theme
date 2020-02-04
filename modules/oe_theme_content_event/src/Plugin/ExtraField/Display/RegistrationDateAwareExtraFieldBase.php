<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\oe_content_event\EventNodeWrapperInterface;

/**
 * Base class for fields that conditionally render on the registration period.
 */
abstract class RegistrationDateAwareExtraFieldBase extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  /**
   * Current request time, as a timestamp.
   *
   * @var int
   */
  protected $requestTime;

  /**
   * Current request time, as a DateTime object.
   *
   * @var \DateTimeInterface
   */
  protected $requestDateTime;

  /**
   * RegistrationDateAwareExtraFieldBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestTime = $time->getRequestTime();
    $this->requestDateTime = (new \DateTime())->setTimestamp($this->requestTime);
  }

  /**
   * Apply max-age depending from the registration period time interval.
   *
   * @param array $build
   *   Render array to apply the max-age to.
   * @param \Drupal\oe_content_event\EventNodeWrapperInterface $event
   *   Event wrapper object.
   */
  protected function applyRegistrationDateMaxAge(array &$build, EventNodeWrapperInterface $event): void {
    $cacheable = CacheableMetadata::createFromRenderArray($build);
    $cacheable->addCacheContexts(['timezone']);

    // Do nothing is the registration is closed.
    if ($event->isRegistrationClosed($this->requestDateTime)) {
      $cacheable->applyTo($build);
      return;
    }

    // Set start date time interval as max-age if registration is yet to come.
    if ($event->isRegistrationPeriodYetToCome($this->requestDateTime)) {
      $cacheable->setCacheMaxAge($event->getRegistrationStartDate()->getTimestamp() - $this->requestTime);
    }

    // Set end date time interval as max-age if registration is in progress.
    if ($event->isRegistrationPeriodActive($this->requestDateTime)) {
      $cacheable->setCacheMaxAge($event->getRegistrationEndDate()->getTimestamp() - $this->requestTime);
    }
    $cacheable->applyTo($build);
  }

}
