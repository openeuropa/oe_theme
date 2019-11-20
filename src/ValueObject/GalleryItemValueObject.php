<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a gallery item.
 */
class GalleryItemValueObject extends ValueObjectBase implements GalleryValueObjectInterface {

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
   * GalleryItemValueObject constructor.
   *
   * @param \Drupal\oe_theme\ValueObject\ValueObjectInterface $thumbnail
   *   Thumbnail to be rendered on the gallery item.
   * @param string|null $caption
   *   Caption for the gallery item.
   * @param string|null $classes
   *   Extra classes for the gallery item.
   * @param string|null $icon
   *   Icon for the gallery item.
   */
  private function __construct(ValueObjectInterface $thumbnail, string $caption = NULL, string $classes = NULL, string $icon = NULL) {
    $this->caption = $caption;
    $this->classes = $classes;
    $this->thumbnail = $thumbnail;
    $this->icon = $icon;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += ['caption' => NULL, 'classes' => NULL, 'icon' => NULL];

    return new static(
      ImageValueObject::fromArray($values['thumbnail']),
      $values['caption'],
      $values['classes'],
      $values['icon']
    );
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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCaption(): ?string {
    return $this->caption;
  }

  /**
   * {@inheritdoc}
   */
  public function getClasses(): ?string {
    return $this->classes;
  }

  /**
   * {@inheritdoc}
   */
  public function getThumbnail(): ImageValueObjectInterface {
    return $this->thumbnail;
  }

  /**
   * {@inheritdoc}
   */
  public function getIcon(): ?string {
    return $this->icon;
  }

}
