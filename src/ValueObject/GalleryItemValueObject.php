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
   * The title of the gallery item.
   *
   * @var string
   */
  protected $title;

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
   * @param string $title
   *   Title for the gallery item.
   */
  private function __construct(ImageValueObjectInterface $thumbnail, string $source, string $type, string $caption = '', string $meta = '', string $title = '') {
    $this->title = $title;
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
      'title' => '',
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
      $values['meta'],
      $values['title']
    );
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getTitle(): string {
    return $this->title;
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
    $thumbnail = $this->getThumbnail()->getArray();
    $values = [
      'thumbnail' => [
        'img' => $thumbnail,
      ],
      'description' => $this->getCaption(),
      'meta' => $this->getMeta(),
      'title' => $this->getTitle(),
      'icon' => 'image',
    ];

    // If an image, use the same thumbnail array which contains the information
    // of the image but set the source as the picture. The source contains the
    // high resolution version of the same image.
    if ($this->getType() === GalleryItemValueObject::TYPE_IMAGE) {
      $thumbnail['src'] = $this->getSource();
      $values['picture'] = [
        'img' => $thumbnail,
      ];
    }

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
