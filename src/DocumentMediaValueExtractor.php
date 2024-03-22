<?php

declare(strict_types=1);

namespace Drupal\oe_theme;

use Drupal\media\MediaInterface;
use Drupal\oe_theme\ValueObject\FileValueObject;

/**
 * Extracts a FileValueObject from a document media.
 */
class DocumentMediaValueExtractor {

  /**
   * Determines and returns a correct FileValueObject from a Document media.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The document media.
   *
   * @return \Drupal\oe_theme\ValueObject\FileValueObject|null
   *   The value object or NULL if could not be determined.
   */
  public static function getFileValue(MediaInterface $media): ?FileValueObject {
    if ($media->bundle() !== 'document') {
      throw new \InvalidArgumentException('The expected media type is Document.');
    }

    $file_type = $media->get('oe_media_file_type')->value;
    if (!$file_type) {
      return NULL;
    }

    if ($file_type === 'remote') {
      return static::getFromRemoteFile($media);
    }

    if ($file_type === 'local') {
      return static::getFromLocalFile($media);
    }

    if ($file_type === 'circabc') {
      return static::getFromCircaBcReference($media);
    }

    return NULL;
  }

  /**
   * Returns a local FileValueObject from a local Document media.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The document media.
   *
   * @return \Drupal\oe_theme\ValueObject\FileValueObject|null
   *   The value object or NULL if could not be determined.
   */
  protected static function getFromLocalFile(MediaInterface $media): ?FileValueObject {
    if ($media->get('oe_media_file')->isEmpty()) {
      return NULL;
    }

    $file_entity = $media->get('oe_media_file')->entity;
    return FileValueObject::fromFileEntity($file_entity)
      ->setTitle($media->getName())
      ->setLanguageCode($media->language()->getId());
  }

  /**
   * Returns a remote FileValueObject from a remote Document media.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The document media.
   *
   * @return \Drupal\oe_theme\ValueObject\FileValueObject|null
   *   The value object or NULL if could not be determined.
   */
  protected static function getFromRemoteFile(MediaInterface $media): ?FileValueObject {
    if ($media->get('oe_media_remote_file')->isEmpty()) {
      return NULL;
    }

    $file_link = $media->get('oe_media_remote_file')->first();

    return FileValueObject::fromFileLink($file_link)
      ->setTitle($media->getName())
      ->setLanguageCode($media->language()->getId());
  }

  /**
   * Returns a remote FileValueObject from a CircaBC Document media.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The document media.
   *
   * @return \Drupal\oe_theme\ValueObject\FileValueObject|null
   *   The value object or NULL if could not be determined.
   */
  protected static function getFromCircaBcReference(MediaInterface $media): ?FileValueObject {
    if ($media->get('oe_media_circabc_reference')->isEmpty()) {
      return NULL;
    }

    $reference = $media->get('oe_media_circabc_reference')->first();

    return FileValueObject::fromArray([
      'name' => $reference->filename,
      'url' => $reference->getFileUrl()->toString(),
      'mime' => $reference->mime,
      'size' => $reference->size,
    ])
      ->setTitle($media->getName())
      ->setLanguageCode($media->language()->getId());
  }

}
