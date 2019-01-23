<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Handle information about a media item.
 */
class ImageMediaValueObject extends MediaValueObject {

  /**
   * The alt of the image.
   *
   * @var string
   */
  protected $alt;

  /**
   * The alt of the image.
   *
   * @var bool
   */
  protected $responsive;

  /**
   * FileType constructor.
   *
   * @param string $name
   *   Name of the file, e.g. "document.pdf".
   * @param string $source
   *   Media URL, including Drupal schema if internal.
   * @param string $alt
   *   Image alt text.
   * @param bool $responsive
   *   Responsiveness of the image.
   */
  public function __construct(string $name, string $source, string $alt, bool $responsive = TRUE) {
    parent::__construct($name, $source);
    $this->alt = $alt;
    $this->responsive = $responsive;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $file = new static(
      $values['name'],
      $values['source'],
      $values['alt'],
      $values['responsive']
    );

    return $file;
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
  public function toArray(): array {
    return [
      'name' => $this->getName(),
      'source' => $this->getSource(),
      'alt' => $this->getAlt(),
      'responsive' => $this->isResponsive(),
    ];
  }

  /**
   * Construct object from a Drupal image field.
   *
   * @param string $name
   *   Name of the image media.
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image_item
   *   Field holding the image media.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *
   * @return $this
   */
  public static function fromImageField($name, ImageItem $image_item): ValueObjectInterface {
    $image_file = $image_item->get('entity')->getTarget();
    $media = new static(
      $name,
      file_create_url($image_file->get('uri')->getString()),
      $image_item->get('alt')->getString()
    );

    return $media;
  }

}
