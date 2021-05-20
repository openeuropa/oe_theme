<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Interface for value objects wrapping files.
 */
interface FileValueObjectInterface extends ValueObjectInterface {

  /**
   * Returns the url.
   *
   * @return string
   *   The file URL, including Drupal schema if internal.
   */
  public function getUrl(): string;

  /**
   * Returns the mime type.
   *
   * @return string
   *   The mime type.
   */
  public function getMime(): string;

  /**
   * Returns the file size.
   *
   * @return string
   *   The file size in bytes.
   */
  public function getSize(): string;

  /**
   * Returns the name.
   *
   * @return string
   *   The file name.
   */
  public function getName(): string;

  /**
   * Returns the title.
   *
   * @return string
   *   The file title.
   */
  public function getTitle(): string;

  /**
   * Returns the language code.
   *
   * @return string
   *   The language code.
   */
  public function getLanguageCode(): string;

  /**
   * Returns the file extension.
   *
   * @return string
   *   The file extension.
   */
  public function getExtension(): string;

  /**
   * Sets the title.
   *
   * @param string $title
   *   The title.
   *
   * @return $this
   */
  public function setTitle(string $title): FileValueObjectInterface;

  /**
   * Sets the language code.
   *
   * @param string $language_code
   *   The language code.
   *
   * @return $this
   */
  public function setLanguageCode(string $language_code): FileValueObjectInterface;

}
