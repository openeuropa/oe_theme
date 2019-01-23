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
   * File Source.
   *
   * @var string
   */
  protected $source;

  /**
   * FileType constructor.
   *
   * @param string $name
   *   Name of the file, e.g. "document.pdf".
   * @param string $source
   *   Media URL, including Drupal schema if internal.
   * @param string $alt
   *   Image alt.
   */
  public function __construct(string $name, string $source, string $alt) {
    parent::__construct($name, $source);
    $this->alt = $alt;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $file = new static(
      $values['name'],
      $values['source'],
      $values['alt']
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
   * {@inheritdoc}
   */
  public function toArray(): array {
    return [
      'name' => $this->getName(),
      'source' => $this->getSource(),
      'alt' => $this->getAlt()
    ];
  }

  /**
   * @param string $name
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image_item
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *
   * @return \Drupal\oe_theme\ValueObject\ValueObjectInterface
   */
  public function fromImageItem($name, ImageItem $image_item): ValueObjectInterface {
    $image_file = $image_item->get('entity')->getTarget();
    $media = new static(
      $name,
      file_create_url($image_file->get('uri')->getString()),
      $image_item->get('alt')->getString()
    );

    return $media;
  }
}
