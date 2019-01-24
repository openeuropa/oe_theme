<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Handle information about a media item.
 */
class MediaValueObject extends ValueObjectBase {

  /**
   * The name of the file.
   *
   * @var string
   */
  protected $name;

  /**
   * File Source.
   *
   * @var string
   */
  protected $source;

  /**
   * FileType constructor.
   *
   * @param string $name
   *   Name of the file, e.g. "document.pdf".
   * @param string $source
   *   Media URL, including Drupal schema if internal.
   */
  public function __construct(string $name, string $source) {
    $this->name = $name;
    $this->source = $source;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $file = new static(
      $values['name'],
      $values['source']
    );

    return $file;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getSource(): string {
    return $this->source;
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
  public function toArray(): array {
    return [
      'name' => $this->getName(),
      'source' => $this->getSource(),
    ];
  }

}
