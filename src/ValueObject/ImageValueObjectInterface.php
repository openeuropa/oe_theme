<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Interface for value objects wrapping images.
 */
interface ImageValueObjectInterface extends ValueObjectInterface {

  /**
   * Returns the image source.
   *
   * @return string
   *   The image source.
   */
  public function getSource(): string;

  /**
   * Returns the image name.
   *
   * @return string
   *   The image name.
   */
  public function getName(): string;

  /**
   * Returns the image alternative text.
   *
   * @return string
   *   The image alternative text.
   */
  public function getAlt(): string;

  /**
   * Returns if the image is responsive.
   *
   * @return bool
   *   Whether or not the image is responsive.
   */
  public function isResponsive(): bool;

}
