<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a gallery item.
 */
class GalleryItemValueObject extends ValueObjectBase {

  /**
   * IMage gallery item type.
   */
  const TYPE_IMAGE = 'image';

  /**
   * Video gallery item type.
   */
  const TYPE_VIDEO = 'video';

  /**
   * Thumbnail of the gallery item.
   *
   * @var \Drupal\oe_theme\ValueObject\ImageValueObject
   */
  protected $thumbnail;

  /**
   * Media source, i.e. the canonical URL to the actual media item.
   *
   * @var string
   */
  protected $source;

  /**
   * Media type, either "video" or "image".
   *
   * @var string
   */
  protected $type;

  /**
   * The caption of the gallery item.
   *
   * @var string
   */
  protected $caption;

  /**
   * Meta information, such as copyright, author, etc.
   *
   * @var string
   */
  protected $meta;

  /**
   * GalleryItemValueObject constructor.
   *
   * @param \Drupal\oe_theme\ValueObject\ValueObjectInterface $thumbnail
   *   Thumbnail to be rendered on the gallery item.
   * @param string $source
   *   Media source, i.e. the canonical URL to the actual media item.
   * @param string $type
   *   Media item type, either 'image' or 'video'.
   * @param string $caption
   *   Caption for the gallery item.
   * @param string $meta
   *   Caption for the gallery item.
   */
  private function __construct(ValueObjectInterface $thumbnail, string $source, string $type, string $caption = '', string $meta = '') {
    $this->caption = $caption;
    $this->thumbnail = $thumbnail;
    $this->meta = $meta;
    $this->source = $source;
    $this->type = $type;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += [
      'thumbnail' => [],
      'source' => '',
      'type' => GalleryItemValueObject::TYPE_IMAGE,
      'caption' => '',
      'meta' => '',
    ];

    return new static(
      ImageValueObject::fromArray($values['thumbnail']),
      $values['source'],
      $values['type'],
      $values['caption'],
      $values['meta']
    );
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getCaption(): string {
    return $this->caption;
  }

  /**
   * Getter.
   *
   * @return \Drupal\oe_theme\ValueObject\ImageValueObject
   *   Property value.
   */
  public function getThumbnail(): ImageValueObject {
    return $this->thumbnail;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getMeta(): string {
    return $this->meta;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getSource(): string {
    return $this->source;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    $values = [
      'image' => $this->getThumbnail()->getArray(),
      'description' => $this->getCaption(),
      'meta' => $this->getMeta(),
      'icon' => '',
    ];

    // If video, then set the required source URL format and icon.
    if ($this->getType() === GalleryItemValueObject::TYPE_VIDEO) {
      $values['icon'] = 'video';
      $values['embedded_video'] = [
        'src' => $this->getSource(),
      ];
    }

    return $values;
  }

}
