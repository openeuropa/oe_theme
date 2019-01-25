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
   * FileType constructor.
   *
   * @param string $icon
   *   Icon for the gallery item.
   * @param string $caption
   *   Caption for the gallery item.
   * @param string $classes
   *   Extra classes for the gallery item.
   * @param \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image
   *   Image to be rendered on the gallery item.
   */
  public function __construct(string $icon, string $caption, string $classes, ImageMediaValueObject $image) {
    $this->icon = $icon;
    $this->caption = $caption;
    $this->classes = $classes;
    $this->image = $image;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getIcon(): string {
    return $this->icon;
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
   * @return string
   *   Property value.
   */
  public function getClasses(): string {
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
  public static function fromArray(array $values = []): ValueObjectInterface {
    $file = new static(
      $values['icon'],
      $values['caption'],
      $values['classes'],
      $values['image']
    );

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    $image = $this->getImage();
    return [
      'icon' => $this->getIcon(),
      'caption' => $this->getCaption(),
      'image' => [
        'src' => $image->getSource(),
        'alt' => $image->getAlt(),
        'responsive' => $image->isResponsive(),
      ],
      'classes' => $this->getCaption(),
    ];
  }

}
