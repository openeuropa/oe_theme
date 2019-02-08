<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a media item.
 */
class MediaValueObject extends ValueObjectBase {

  /**
   * The name of the media.
   *
   * @var string
   */
  protected $name;

  /**
   * Media Source.
   *
   * @var string
   */
  protected $src;

  /**
   * MediaType constructor.
   *
   * @param string $src
   *   Media URL, including Drupal schema if internal.
   * @param string $name
   *   Name of the media, e.g. "example.xxx".
   */
  public function __construct(string $src, string $name = '') {
    $this->name = $name;
    $this->src = $src;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += ['name' => ''];

    $object = new static(
      $values['src'],
      $values['name']
    );

    return $object;
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
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'src' => $this->getSource(),
      'name' => $this->getName(),
    ];
  }

}
