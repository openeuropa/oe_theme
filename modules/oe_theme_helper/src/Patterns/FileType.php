<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Patterns;

use Drupal\file\Entity\File;
use Drupal\file\FileInterface;

/**
 * FileType value object for pattern field type "file".
 */
class FileType implements FieldTypeInterface {

  /**
   * The name of the file.
   *
   * @var string
   */
  protected $name;

  /**
   * File URL.
   *
   * @var string
   */
  protected $url;

  /**
   * The file mime type.
   *
   * @var string
   */
  protected $mime;

  /**
   * The size of the file.
   *
   * @var string
   */
  protected $size;

  /**
   * File name extension.
   *
   * @var string
   */
  protected $extension;

  /**
   * File title.
   *
   * @var string
   */
  protected $title;

  /**
   * Language code.
   *
   * @var string
   */
  protected $languageCode = '';

  /**
   * FileType constructor.
   *
   * @param string $name
   *   Name of the file, e.g. "document.pdf".
   * @param string $url
   *   File URL, including Drupal schema if internal.
   * @param string $mime
   *   File mime type.
   * @param string $size
   *   File size in bytes.
   */
  private function __construct(string $name, string $url, string $mime, string $size) {
    $this->name = $name;
    $this->url = $url;
    $this->mime = $mime;
    $this->size = $size;
  }

  /**
   * Construct object from a Drupal file entity.
   *
   * @param \Drupal\file\FileInterface $file_entity
   *   Drupal file entity object.
   *
   * @return $this
   */
  public static function fromFileEntity(FileInterface $file_entity): FileType {
    $file = new static(
      $file_entity->getFilename(),
      $file_entity->getFileUri(),
      $file_entity->getMimeType(),
      (string) $file_entity->getSize()
    );

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): FieldTypeInterface {
    $file = new static(
      $values['name'],
      $values['url'],
      $values['mime'],
      $values['size']
    );

    if (isset($values['title'])) {
      $file->setTitle($values['title']);
    }

    if (isset($values['language_code'])) {
      $file->setLanguageCode($values['language_code']);
    }

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public static function getInstance($value): FieldTypeInterface {
    if (is_array($value)) {
      $file = FileType::fromArray($value);
    }
    elseif (is_object($value) && $value instanceof File) {
      $file = FileType::fromFileEntity($value);
    }
    else {
      throw new FieldTypeFactoryException(FileType::class);
    }

    return $file;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getMime(): string {
    return $this->mime;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getSize(): string {
    return $this->size;
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
  public function getTitle(): string {
    return $this->title ? $this->title : $this->name;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getLanguageCode(): string {
    return $this->languageCode;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getExtension(): string {
    return pathinfo($this->name, PATHINFO_EXTENSION);
  }

  /**
   * Setter.
   *
   * @param string $title
   *   Property value.
   *
   * @return $this
   */
  public function setTitle(string $title): FileType {
    $this->title = $title;

    return $this;
  }

  /**
   * Setter.
   *
   * @param string $language_code
   *   Property value.
   *
   * @return $this
   */
  public function setLanguageCode(string $language_code): FileType {
    $this->languageCode = $language_code;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    return [
      'title' => $this->getTitle(),
      'name' => $this->getName(),
      'url' => $this->getUrl(),
      'size' => $this->getSize(),
      'mime' => $this->getMime(),
      'extension' => $this->getExtension(),
      'language_code' => $this->getLanguageCode(),
    ];
  }

}
