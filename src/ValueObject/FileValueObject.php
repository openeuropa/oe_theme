<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\file\FileInterface;

/**
 * Handle information about a file, such as its mime type, size, language, etc.
 *
 * @method extension()
 * @method language_code()
 * @method mime()
 * @method name()
 * @method size()
 * @method title()
 * @method url()
 */
class FileValueObject extends ValueObjectBase {

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
    $this->storage = compact([
      'name',
      'url',
      'mime',
      'size',
    ]);

    $this->storage['title'] = $this->storage['name'];
    $this->storage['extension'] = pathinfo(
      $this->storage['name'],
      PATHINFO_EXTENSION
    );
    $this->storage['language_code'] = '';
  }

  /**
   * Construct object from a Drupal file entity.
   *
   * @param \Drupal\file\FileInterface $file_entity
   *   Drupal file entity object.
   *
   * @return $this
   */
  public static function fromFileEntity(FileInterface $file_entity): FileValueObject {
    $file = new static(
      $file_entity->getFilename(),
      file_create_url($file_entity->getFileUri()),
      $file_entity->getMimeType(),
      (string) $file_entity->getSize()
    );

    return $file->withLanguageCode($file_entity->language()->getId());
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
      $file = $file->withTitle($values['title']);
    }

    if (isset($values['language_code'])) {
      $file = $file->withLanguageCode($values['language_code']);
    }

    return $file;
  }

  /**
   * Create a new FileValueObject with a specific title.
   *
   * @param string $title
   *   Title value.
   *
   * @return \Drupal\oe_theme\ValueObject\FileValueObject
   *   A new FileValueObject.
   */
  public function withTitle(string $title): FileValueObject {
    $clone = clone $this;

    $clone->storage['title'] = $title;

    return $clone;
  }

  /**
   * Create a new FileValueObject with a specific language code.
   *
   * @param string $languageCode
   *   Property value.
   *
   * @return $this
   */
  public function withLanguageCode(string $languageCode): FileValueObject {
    $clone = clone $this;

    $clone->storage['language_code'] = $languageCode;

    return $clone;
  }

}
