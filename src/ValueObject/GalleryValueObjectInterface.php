<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Interface GalleryValueObjectInterface.
 */
interface GalleryValueObjectInterface extends ValueObjectInterface {

  /**
   * Get the caption text.
   *
   * @return string
   *   Property value.
   */
  public function getCaption(): ?string;

  /**
   * Get the css classes.
   *
   * @return string
   *   Property value.
   */
  public function getClasses(): ?string;

  /**
   * Get the thumbnail image.
   *
   * @return \Drupal\oe_theme\ValueObject\ImageValueObjectInterface
   *   Property value.
   */
  public function getThumbnail(): ImageValueObjectInterface;

  /**
   * Get the icon.
   *
   * @return string
   *   Property value.
   */
  public function getIcon(): ?string;

}
