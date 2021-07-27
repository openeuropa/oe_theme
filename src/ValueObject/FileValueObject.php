<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\file\FileInterface;
use Drupal\file_link\Plugin\Field\FieldType\FileLinkItem;
use Mimey\MimeTypes;

/**
 * Handle information about a file, such as its mime type, size, language, etc.
 */
class FileValueObject extends ValueObjectBase {

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
  public static function fromFileEntity(FileInterface $file_entity): ValueObjectInterface {
    $file = new static(
      $file_entity->getFilename(),
      file_create_url($file_entity->getFileUri()),
      $file_entity->getMimeType(),
      (string) $file_entity->getSize()
    );

    $file->setLanguageCode($file_entity->language()->getId());
    $file->addCacheableDependency($file_entity);

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
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
   * Constructs an object from file link.
   *
   * @param \Drupal\file_link\Plugin\Field\FieldType\FileLinkItem $link
   *   The file link item.
   *
   * @return $this
   */
  public static function fromFileLink(FileLinkItem $link): ValueObjectInterface {
    $file = new static(
      $link->get('title')->getValue() ?? '',
      $link->get('uri')->getValue(),
      // The file_link module can be configured to link to basically anything
      // so we have to have a fallback format in case one is not determined.
      // This is usually going to be a basic HTML page.
      $link->getFormat() ?? 'text/html',
      (string) $link->getSize()
    );

    $file->setLanguageCode($link->getEntity()->language()->getId());

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
    $mime_types = new MimeTypes();
    $extension = $mime_types->getExtension($this->getMime());
    return $extension ?? pathinfo($this->name, PATHINFO_EXTENSION);
  }

  /**
   * Setter.
   *
   * @param string $title
   *   Property value.
   *
   * @return $this
   */
  public function setTitle(string $title): FileValueObject {
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
  public function setLanguageCode(string $language_code): FileValueObject {
    $this->languageCode = $language_code;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
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
