<?php

namespace Drupal\oe_theme_helper\EventSubscriber;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Image\Image;
use Drupal\media\MediaInterface;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalSourceInterface;
use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme_helper\Event\PatternGenerationEvent;
use Drupal\oe_theme_helper\PatternGenerator\PatternGeneratorMediaTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParagraphPatternGenerationSubscriber implements EventSubscriberInterface {

  use PatternGeneratorMediaTrait;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [PatternGenerationEvent::NAME => ['getPatternFields', 0]];
  }

  public function getPatternFields(PatternGenerationEvent $event) {
    if ($event->getEntity() !== 'paragraph') {
      return;
    }

    switch ($event->getEntity()->bundle()) {
      case 'oe_list_item':
        $this->processItemListFields($event);
        break;
      case 'oe_text_feature_media':
        $this->processFeaturedItemFields($event);
        break;
    }
  }

  protected function processItemListFields(PatternGenerationEvent $event) {
    $paragraph = $event->getEntity();
    $fields = [];
    $fields['url'] = $paragraph->get('field_oe_link')->first()->getUrl();

    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($paragraph);

    // Extract the image if present.
    if (!$paragraph->get('field_oe_image')->isEmpty()) {
      $fields['image'] = $this->processImageField($paragraph->get('field_oe_image')->first(), $cache);
    }

    // Prepare the date fields if date is available.
    if (!$paragraph->get('field_oe_date')->isEmpty()) {
      $fields['date'] = DateValueObject::fromDateTimeItem($paragraph->get('field_oe_date')->first());

      // Add the timezone context to the cache.
      // @see \Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeFormatterBase::buildDate()
      $cache->addCacheContexts(['timezone']);
    }

    // Prepare the metas if available.
    if (!$paragraph->get('field_oe_meta')->isEmpty()) {
      $metas = [];
      foreach ($paragraph->get('field_oe_meta') as $item) {
        $metas[] = $item->value;
      }
      $fields['meta'] = $metas;
    }

    $event->setFields($fields);
    $event->addCacheableDependency($cache);
  }

  protected function processFeaturedItemFields(PatternGenerationEvent $event) {
    $paragraph = $event->getEntity();
    $fields = [];

    if ($paragraph->get('field_oe_media')->isEmpty()) {
      return $fields;
    }

    /** @var \Drupal\media\Entity\Media $media */
    $media = $paragraph->get('field_oe_media')->entity;
    if (!$media instanceof MediaInterface) {
      // The media entity is not available anymore, bail out.
      return $fields;
    }

    $cache = new CacheableMetadata();

    $fields['image'] = $this->processImageBasedMediaField($paragraph->get('field_oe_media')->first(), $cache, 'oe_theme_medium_no_crop');
    $event->setFields($fields);
  }
}
