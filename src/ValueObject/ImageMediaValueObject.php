<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Handle information about an image media item.
 */
class ImageMediaValueObject extends MediaValueObject {

  /**
   * The alt of the image.
   *
   * @var string
   */
  protected $alt;

  /**
   * The parameter 'responsive' of the image.
   *
   * @var bool
   */
  protected $responsive;

  /**
   * ImageMediaValueObject constructor.
   *
   * @param string $src
   *   Media URL, including Drupal schema if internal.
   * @param string $alt
   *   Image alt text.
   * @param string $name
   *   Name of the image, e.g. "example.jpg".
   * @param bool $responsive
   *   Responsiveness of the image.
   */
  protected function __construct(string $src, string $alt = '', string $name = '', bool $responsive = TRUE) {
    parent::__construct($src, $name);
    $this->alt = $alt;
    $this->responsive = $responsive;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += ['alt' => '', 'name' => '', 'responsive' => TRUE];

    $object = new static(
      $values['src'],
      $values['alt'],
      $values['name'],
      $values['responsive']
    );

    return $object;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getAlt(): string {
    return $this->alt;
  }

  /**
   * Getter.
   *
   * @return bool
   *   Property value.
   */
  public function isResponsive(): bool {
    return $this->responsive;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'name' => $this->getName(),
      'src' => $this->getSource(),
      'alt' => $this->getAlt(),
      'responsive' => $this->isResponsive(),
    ];
  }

  /**
   * Construct object from a Drupal image field.
   *
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image_item
   *   Field holding the image media.
   * @param string $name
   *   Name of the image media.
   * @param bool $responsive
   *   Whether the image should be responsive or not.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *
   * @return $this
   */
  public static function fromImageField(ImageItem $image_item, $name = '', $responsive = TRUE): ValueObjectInterface {
    $image_file = $image_item->get('entity')->getTarget();
    $media = new static(
      file_create_url($image_file->get('uri')->getString()),
      $image_item->get('alt')->getString(),
      $name,
      $responsive
    );

    return $media;
  }

}
