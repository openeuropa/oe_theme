<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Handle information about an image, such as source, alt, name and responsive.
 */
class ImageValueObject extends ValueObjectBase {

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
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getSource(): string {
    return $this->src;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getName(): string {
    return $this->name;
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
   * @return bool
   *   Property value.
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
   * Construct object from a Drupal image field.
   *
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image_item
   *   Field holding the image.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *
   * @return $this
   */
  public static function fromImageItem(ImageItem $image_item): ValueObjectInterface {
    $image_file = $image_item->get('entity')->getTarget();

    return new static(
      file_create_url($image_file->get('uri')->getString()),
      $image_item->get('alt')->getString(),
      $image_item->get('title')->getString()
    );
  }

  /**
   * Construct object from a styled image.
   *
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image_item
   *   Field holding the image.
   * @param string $style
   *   The style.
   *
   * @return $this
   */
  public static function fromStyledImageItem(ImageItem $image_item, string $style): ValueObjectInterface {
    $image_file = $image_item->get('entity')->getTarget();
    $style = \Drupal::entityTypeManager()->getStorage('image_style')->load($style);

    return new static(
      $style->buildUrl($image_file->get('uri')->getString()),
      $image_item->get('alt')->getString(),
      $image_item->get('title')->getString()
    );
  }

}
