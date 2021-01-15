<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\media\Entity\Media;

/**
 * Handle information about a media element.
 */
interface MediaValueObjectInterface extends ValueObjectInterface {

  /**
   * Video aspect ratio allowed values.
   *
   * @var array
   */
  public const ALLOWED_VALUES = ['16-9', '4-3', '3-2', '1-1'];

  /**
   * Video aspect default ratio.
   *
   * @var string
   */
  public const DEFAULT_RATIO = '16-9';

  /**
   * Get the video attribute value.
   *
   * @return string
   *   The video attribute value.
   */
  public function getVideo(): string;

  /**
   * Get the ratio attribute value.
   *
   * @return string
   *   Video ratio.
   */
  public function getRatio(): string;

  /**
   * Get the sources attribute value.
   *
   * @return array
   *   The video sources.
   */
  public function getSources(): array;

  /**
   * Get the tracks attribute value.
   *
   * @return array
   *   Video tracks.
   */
  public function getTracks(): array;

  /**
   * Get the image attribute value.
   *
   * @return ImageValueObjectInterface|null
   *   The image object or null.
   */
  public function getImage(): ?ImageValueObjectInterface;

  /**
   * Construct object from a Drupal Media object.
   *
   * @param \Drupal\media\Entity\Media $media
   *   Drupal Media element.
   * @param string $image_style
   *   Image style.
   * @param string $view_mode
   *   Video display view mode.
   *
   * @return $this
   *   A media value object.
   */
  public static function fromMediaObject(Media $media, string $image_style = '', string $view_mode = ''): MediaValueObjectInterface;

  /**
   * Validate and transform aspect ratio.
   *
   * @param string $ratio
   *   The video ratio to validate.
   *
   * @return string
   *   The transformed video ratio.
   */
  public static function validateRatio(string $ratio): string;

}
