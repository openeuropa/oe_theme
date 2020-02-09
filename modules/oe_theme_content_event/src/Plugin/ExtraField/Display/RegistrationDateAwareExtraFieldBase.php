<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGeneratorInterface;

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
   * Time based cache tag generator service.
   *
   * @var \Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGeneratorInterface
   */
  protected $cacheTagGenerator;

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
   * @param \Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
   *   Time based cache tag generator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time, TimeBasedCacheTagGeneratorInterface $cache_tag_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestTime = $time->getRequestTime();
    $this->requestDateTime = (new \DateTime())->setTimestamp($this->requestTime);
    $this->cacheTagGenerator = $cache_tag_generator;
  }

  /**
   * Apply current hour invalidation tag.
   *
   * @param array $build
   *   Render array to apply the max-age to.
   * @param \Drupal\Core\Datetime\DrupalDateTime $datetime
   *   Datetime used to generate invalidation tag.
   */
  protected function applyHourTag(array &$build, DrupalDateTime $datetime): void {
    $cacheable = CacheableMetadata::createFromRenderArray($build);
    $cacheable->addCacheContexts(['timezone']);
    $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($datetime->getPhpDateTime()));
    $cacheable->applyTo($build);
  }

  /**
   * Apply midnight invalidation tag.
   *
   * @param array $build
   *   Render array to apply the max-age to.
   * @param \Drupal\Core\Datetime\DrupalDateTime $datetime
   *   Datetime used to generate invalidation tag.
   */
  protected function applyMidnightTag(array &$build, DrupalDateTime $datetime): void {
    $cacheable = CacheableMetadata::createFromRenderArray($build);
    $cacheable->addCacheContexts(['timezone']);
    $cacheable->addCacheTags($this->cacheTagGenerator->generateTagsUntilMidnight($datetime->getPhpDateTime()));
    $cacheable->applyTo($build);
  }

}
