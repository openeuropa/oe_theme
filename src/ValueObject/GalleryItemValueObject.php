<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a gallery item.
 */
class GalleryItemValueObject extends ValueObjectBase {

  /**
   * The path to the gallery item.
   *
   * @var string
   */
  protected $path;

  /**
   * The alternative to the gallery item.
   *
   * @var string
   */
  protected $alt;

  /**
   * The description of the gallery item.
   *
   * @var string
   */
  protected $description;

  /**
   * The metadata of the gallery item.
   *
   * @var string
   */
  protected $meta;

  /**
   * The icon associated with the gallery item.
   *
   * @var array
   */
  protected $icon;

  /**
   * The path to share the item.
   *
   * @var string
   */
  protected $sharePath;

  /**
   * GalleryItemValueObject constructor.
   *
   * @param string $path
   *   The path to the item.
   * @param string $alt
   *   The alternative text of the item.
   * @param string $description
   *   The description of the item.
   * @param string $meta
   *   The metadata of the item.
   * @param array $icon
   *   The icon of the item.
   * @param string $share_path
   *   The path to share the item.
   */
  private function __construct(string $path, string $alt = '', string $description = '', string $meta = '', array $icon = [], string $share_path = '') {
    $this->path = $path;
    $this->alt = $alt;
    $this->description = $description;
    $this->meta = $meta;
    $this->icon = $icon;
    $this->sharePath = $share_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += [
      'alt' => '',
      'description' => '',
      'meta' => '',
      'share_path' => '',
      'icon' => [],
    ];

    return new static(
      $values['path'],
      $values['alt'],
      $values['description'],
      $values['meta'],
      $values['icon'],
      $values['share_path']
    );
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getPath(): string {
    return $this->path;
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
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getDescription(): string {
    return $this->description;
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
   * @return []]
   *   Property value.
   */
  public function getIcon() {
    return $this->icon;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getSharePath(): string {
    return $this->sharePath;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'path' => $this->getPath(),
      'alt' => $this->getAlt(),
      'description' => $this->getDescription(),
      'meta' => $this->getMeta(),
      'icon' => $this->getIcon(),
      'share_path' => $this->getSharePath(),
    ];
  }

}
