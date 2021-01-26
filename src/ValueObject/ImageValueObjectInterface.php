<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Interface for handling image related information.
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
   * Get the image name.
   *
   * @return string
   *   Property value.
   */
  public function getName(): string;

  /**
   * Get the image alternative text.
   *
   * @return string
   *   Property value.
   */
  public function getAlt(): string;

  /**
   * Get the image responsive flag.
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
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *
   * @return $this
   */
  public static function fromImageItem(ImageItem $image_item): ImageValueObjectInterface;

  /**
   * Construct object from a Drupal image field and image style.
   *
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image_item
   *   The field image item instance.
   * @param string $style_name
   *   The image style name.
   *
   * @return $this
   *   A image value object instance.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the image style is not found.
   */
  public static function fromStyledImageItem(ImageItem $image_item, string $style_name): ImageValueObjectInterface;

}
