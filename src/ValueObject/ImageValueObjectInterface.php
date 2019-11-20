<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Interface ImageValueObjectInterface.
 */
interface ImageValueObjectInterface extends ValueObjectInterface {
  /**
   * Get the image source.
   *
   * @return string
   *   Property value.
   */
  public function getSource(): string;

  /**
   * Get the name of the image.
   *
   * @return string
   *   Property value.
   */
  public function getName(): string;

  /**
   * Get the alt text.
   *
   * @return string
   *   Property value.
   */
  public function getAlt(): string;

  /**
   * Get if the image is responsive or not.
   *
   * @return bool
   *   Property value.
   */
  public function isResponsive(): bool;

  /**
   * Construct object from a Drupal image field.
   *
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image_item
   *   Field holding the image.
   *
   * @return $this
   */
  public static function fromImageItem(ImageItem $image_item): ImageValueObjectInterface;
}
