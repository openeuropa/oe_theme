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
   * Extra classes of the gallery item.
   *
   * @var \Drupal\oe_theme\ValueObject\ImageValueObject
   */
  protected $image;

  /**
   * Icon of the gallery item.
   *
   * @var string
   */
  protected $icon;

  /**
   * GalleryItemValueObject constructor.
   *
   * @param \Drupal\oe_theme\ValueObject\ImageValueObject $image
   *   Image to be rendered on the gallery item.
   * @param string|null $caption
   *   Caption for the gallery item.
   * @param string|null $classes
   *   Extra classes for the gallery item.
   * @param string|null $icon
   *   Icon for the gallery item.
   */
  private function __construct(ImageValueObject $image, string $caption = NULL, string $classes = NULL, string $icon = NULL) {
    $this->caption = $caption;
    $this->classes = $classes;
    $this->image = $image;
    $this->icon = $icon;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += ['caption' => NULL, 'classes' => NULL, 'icon' => NULL];

    $object = new static(
      $values['image'],
      $values['caption'],
      $values['classes'],
      $values['icon']
    );

    return $object;
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
  public function getImage(): ImageValueObject {
    return $this->image;
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
   * {@inheritdoc}
   */
  public function getArray(): array {
    /** @var \Drupal\oe_theme\ValueObject\ImageValueObject $image */
    $image = $this->getImage();

    return [
      'image' => $image->getArray(),
      'caption' => $this->getCaption(),
      'classes' => $this->getClasses(),
      'icon' => $this->getIcon(),
    ];
  }

}
