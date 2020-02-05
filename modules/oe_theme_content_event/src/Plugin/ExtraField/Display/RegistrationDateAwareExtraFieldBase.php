<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

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
   * Apply max-age relative to a given timestamp.
   *
   * @param array $build
   *   Render array to apply the max-age to.
   * @param int $timestamp
   *   Date timestamp used to calculate relative max-age.
   */
  protected function applyRelativeMaxAge(array &$build, int $timestamp): void {
    $cacheable = CacheableMetadata::createFromRenderArray($build);
    $cacheable->addCacheContexts(['timezone']);
    $cacheable->setCacheMaxAge($timestamp - $this->requestTime);
    $cacheable->applyTo($build);
  }

  /**
   * Apply max-age calculated as midnight minus a given timestamp.
   *
   * @param array $build
   *   Render array to apply the max-age to.
   * @param int $timestamp
   *   Date timestamp used to calculate relative max-age.
   */
  protected function applyMidnightRelativeMaxAge(array &$build, int $timestamp): void {
    $cacheable = CacheableMetadata::createFromRenderArray($build);
    $cacheable->addCacheContexts(['timezone']);

    // Get the timestamp of today at 1 second to midnight.
    $midnight = (new \DateTime())
      ->setTimestamp($this->requestTime)
      ->setTime(23, 59, 59)
      ->getTimestamp();
    $cacheable->setCacheMaxAge($midnight - $timestamp);
    $cacheable->applyTo($build);
  }

}
