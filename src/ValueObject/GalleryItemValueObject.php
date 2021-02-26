<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a gallery item.
 */
class GalleryItemValueObject extends ValueObjectBase {

  /**
   * The caption of the gallery item.
   *
   * @var string
   */
  protected $caption;

  /**
   * Extra classes of the gallery item.
   *
   * @var string
   */
  protected $classes;

  /**
   * Thumbnail of the gallery item.
   *
   * @var \Drupal\oe_theme\ValueObject\ImageValueObject
   */
  protected $thumbnail;

  /**
   * Icon of the gallery item.
   *
   * @var string
   */
  protected $icon;

  /**
   * Meta information, such as copyright, author, etc.
   *
   * @var string
   */
  protected $meta;

  /**
   * GalleryItemValueObject constructor.
   *
   * @param \Drupal\oe_theme\ValueObject\ImageValueObject $thumbnail
   *   Thumbnail to be rendered on the gallery item.
   * @param string|null $caption
   *   Caption for the gallery item.
   * @param string|null $classes
   *   Extra classes for the gallery item.
   * @param string|null $icon
   *   Icon for the gallery item.
   * @param string|null $meta
   *   Meta information, such as copyright, author, etc.
   */
  private function __construct(ImageValueObject $thumbnail, string $caption = NULL, string $classes = NULL, string $icon = NULL, string $meta = NULL) {
    $this->caption = $caption;
    $this->classes = $classes;
    $this->thumbnail = $thumbnail;
    $this->icon = $icon;
    $this->meta = $meta;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += [
      'caption' => NULL,
      'classes' => NULL,
      'icon' => NULL,
      'meta' => NULL,
    ];

    return new static(
      ImageValueObject::fromArray($values['thumbnail']),
      $values['caption'],
      $values['classes'],
      $values['icon'],
      $values['meta']
    );
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getCaption(): ?string {
    return $this->caption;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getClasses(): ?string {
    return $this->classes;
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
  public function getIcon(): ?string {
    return $this->icon;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getMeta(): ?string {
    return $this->meta;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    /** @var \Drupal\oe_theme\ValueObject\ImageValueObject $thumbnail */
    $thumbnail = $this->getThumbnail();

    return [
      'thumbnail' => $thumbnail->getArray(),
      'caption' => $this->getCaption(),
      'classes' => $this->getClasses(),
      'icon' => $this->getIcon(),
      'meta' => $this->getMeta(),
    ];
  }

}
