<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a media item.
 */
class GalleryValueObject extends ValueObjectBase {

  /**
   * The icon of the gallery item.
   *
   * @var string
   */
  protected $icon;

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
   * Extra classes of the gallery item.
   *
   * @var \Drupal\oe_theme\ValueObject\ImageMediaValueObject
   */
  protected $image;

  /**
   * GalleryType constructor.
   *
   * @param \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image
   *   Image to be rendered on the gallery item.
   * @param string|null $icon
   *   Icon for the gallery item.
   * @param string|null $caption
   *   Caption for the gallery item.
   * @param string|null $classes
   *   Extra classes for the gallery item.
   */
  public function __construct(ImageMediaValueObject $image, string $icon = NULL, string $caption = NULL, string $classes = NULL) {
    $this->icon = $icon;
    $this->caption = $caption;
    $this->classes = $classes;
    $this->image = $image;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += ['icon' => NULL, 'caption' => NULL, 'classes' => NULL];

    $object = new static(
      $values['image'],
      $values['icon'],
      $values['caption'],
      $values['classes']
    );

    return $object;
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
   * @return \Drupal\oe_theme\ValueObject\ImageMediaValueObject
   *   Property value.
   */
  public function getImage(): ImageMediaValueObject {
    return $this->image;
  }

  /**
   * Setter.
   *
   * @param string $icon
   *   ECL icon name.
   *
   * @return \Drupal\oe_theme\ValueObject\GalleryValueObject
   *   A new ValueObject object.
   */
  public function setIcon(string $icon): GalleryValueObject {
    $this->icon = $icon;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    /** @var \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image */
    $image = $this->getImage();

    return [
      'image' => $image->getArray(),
      'icon' => $this->getIcon(),
      'caption' => $this->getCaption(),
      'classes' => $this->getClasses(),
    ];
  }

}
