<?php

declare(strict_types=1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a gallery item.
 *
 * @internal
 */
class GalleryItemValueObject extends ValueObjectBase {

  /**
   * Image gallery item type.
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
   * @param \Drupal\oe_theme\ValueObject\ImageValueObjectInterface $thumbnail
   *   Thumbnail to be rendered on the gallery item.
   * @param string $source
   *   Media source, i.e. the canonical URL to the actual media item.
   * @param string $type
   *   Media item type, either 'image' or 'video'.
   * @param string $caption
   *   Caption for the gallery item.
   * @param string $meta
   *   Meta for the gallery item, such as a copyright note.
   */
  private function __construct(ImageValueObjectInterface $thumbnail, string $source, string $type, string $caption = '', string $meta = '') {
    $this->caption = $caption;
    $this->thumbnail = $thumbnail;
    $this->meta = $meta;
    $this->source = $source;
    $this->type = $type;

    $this->addCacheableDependency($thumbnail);
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

    // @todo Maybe expect always a thumbnail object instead of also an array,
    //   so that cacheability information can be propagated.
    if (is_array($values['thumbnail'])) {
      $values['thumbnail'] = ImageValueObject::fromArray($values['thumbnail']);
    }

    return new static(
      $values['thumbnail'],
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
    // Image media items are displayed using the image passed as thumbnail.
    // This is due to the fact that the ECL gallery component does not yet
    // support having a low and high resolution version of the same image.
    // This makes it so that, for images, the source property is effectively
    // ignored, while it is used for videos.
    $values = [
      'picture' => [
        'img' => $this->getThumbnail()->getArray(),
      ],
      'description' => $this->getCaption(),
      'meta' => $this->getMeta(),
      'icon' => 'image',
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
