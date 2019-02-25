<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a gallery item.
 */
class GalleryItemValueObject extends ValueObjectBase {

  /**
   * The icon of the gallery item.
   *
   * @var string
   */
  const ICON = 'camera';

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
   * GalleryItemValueObject constructor.
   *
   * @param \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image
   *   Image to be rendered on the gallery item.
   * @param string|null $caption
   *   Caption for the gallery item.
   * @param string|null $classes
   *   Extra classes for the gallery item.
   */
  protected function __construct(ImageMediaValueObject $image, string $caption = NULL, string $classes = NULL) {
    $this->caption = $caption;
    $this->classes = $classes;
    $this->image = $image;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += ['caption' => NULL, 'classes' => NULL];

    $object = new static(
      $values['image'],
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
   * {@inheritdoc}
   */
  public function getArray(): array {
    /** @var \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image */
    $image = $this->getImage();

    return [
      'image' => $image->getArray(),
      'icon' => $this::ICON,
      'caption' => $this->getCaption(),
      'classes' => $this->getClasses(),
    ];
  }

}
