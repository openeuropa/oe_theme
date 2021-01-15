<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Handle information about an image, such as source, alt, name and responsive.
 */
class ImageValueObject extends ValueObjectBase implements ImageValueObjectInterface {

  /**
   * Image Source.
   *
   * @var string
   */
  protected $src;

  /**
   * The alt of the image.
   *
   * @var string
   */
  protected $alt;

  /**
   * The name of the Image.
   *
   * @var string
   */
  protected $name;

  /**
   * The parameter 'responsive' of the image.
   *
   * @var bool
   */
  protected $responsive;

  /**
   * ImageValueObject constructor.
   *
   * @param string $src
   *   Image URL, including Drupal schema if internal.
   * @param string $alt
   *   Image alt text.
   * @param string $name
   *   Name of the image, e.g. "example.jpg".
   * @param bool $responsive
   *   Responsiveness of the image.
   */
  private function __construct(string $src, string $alt = '', string $name = '', bool $responsive = TRUE) {
    $this->src = $src;
    $this->alt = $alt;
    $this->name = $name;
    $this->responsive = $responsive;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += ['alt' => '', 'name' => '', 'responsive' => TRUE];

    return new static(
      $values['src'],
      $values['alt'],
      $values['name'],
      $values['responsive']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSource(): string {
    return $this->src;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlt(): string {
    return $this->alt;
  }

  /**
   * {@inheritdoc}
   */
  public function isResponsive(): bool {
    return $this->responsive;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'name' => $this->getName(),
      'src' => $this->getSource(),
      'alt' => $this->getAlt(),
      'responsive' => $this->isResponsive(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function fromImageItem(ImageItem $image_item): ImageValueObjectInterface {
    $image_file = $image_item->get('entity')->getTarget();

    $image_object = new static(
      file_create_url($image_file->get('uri')->getString()),
      $image_item->get('alt')->getString(),
      $image_item->get('title')->getString()
    );

    $image_object->addCacheableDependency($image_file);

    return $image_object;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromStyledImageItem(ImageItem $image_item, string $style_name): ImageValueObjectInterface {
    $image_file = $image_item->get('entity')->getTarget();

    $style = \Drupal::entityTypeManager()->getStorage('image_style')->load($style_name);
    if (!$style) {
      throw new \InvalidArgumentException(sprintf('Could not load image style with name "%s".', $style_name));
    }

    $image_object = new static(
      $style->buildUrl($image_file->get('uri')->getString()),
      $image_item->get('alt')->getString(),
      $image_item->get('title')->getString()
    );

    $image_object->addCacheableDependency($image_file);
    $image_object->addCacheableDependency($style);

    return $image_object;
  }

}
