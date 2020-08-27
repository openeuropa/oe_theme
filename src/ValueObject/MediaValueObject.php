<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalVideoSource;
use Drupal\oe_media_iframe\Plugin\media\Source\Iframe;
use Drupal\media\Entity\Media;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\media\Plugin\media\Source\OEmbed;

/**
 * Handle information about an media element.
 */
class MediaValueObject extends ValueObjectBase {

  /**
   * Embed video code.
   *
   * @var string
   */
  protected $embeddedMedia;

  /**
   * Video aspect ratio.
   *
   * @var string
   */
  protected $ratio;

  /**
   * Image.
   *
   * @var ImageValueObject
   */
  protected $image;

  /**
   * Media sources.
   *
   * @var array
   */
  protected $sources;

  /**
   * Media tracks.
   *
   * @var array
   */
  protected $tracks;

  /**
   * Media caption.
   *
   * @var string
   */
  protected $description;

  /**
   * Enable debug.
   *
   * @var bool
   */
  protected $compliance;

  /**
   * MediaValueObject constructor.
   *
   * @param string $embedded_media
   *   HTLM code to embed.
   * @param string $ratio
   *   Video aspect ratio.
   * @param ImageValueObject $image
   *   Image value object.
   * @param array $sources
   *   Media sources.
   * @param array $tracks
   *   Video tracks.
   * @param string $description
   *   Media caption.
   * @param bool $compliance
   *   Enable debug.
   */
  private function __construct(string $embedded_media = '', string $ratio = '', ImageValueObject $image = NULL, array $sources = [], array $tracks = [], string $description = '', bool $compliance = FALSE) {
    $this->embeddedMedia = $embedded_media;
    $this->ratio = $ratio;
    $this->image = $image;
    $this->sources = $sources;
    $this->tracks = $tracks;
    $this->description = $description;
    $this->compliance = $compliance;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    return new static(
      $values['embedded_media'],
      $values['ratio'],
      $values['image'],
      $values['sources'],
      $values['tracks'],
      $values['description'],
      $values['compliance']
    );
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getEmbedMedia(): string {
    return $this->embeddedMedia;
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getRatio(): string {
    return $this->ratio;
  }

  /**
   * Getter.
   *
   * @return ImageValueObject
   *   Property value.
   */
  public function getImage() {
    return $this->image;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getSources(): array {
    return $this->sources;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getTracks(): array {
    return $this->tracks;
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
   * @return bool
   *   Property value.
   */
  public function isCompliance(): bool {
    return $this->compliance;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'embedded_media' => $this->getEmbedMedia(),
      'ratio' => $this->getRatio(),
      'image' => $this->getImage(),
      'sources' => $this->getSources(),
      'tracks' => $this->getTracks(),
      'description' => $this->getDescription(),
      'compliance' => $this->isCompliance(),
    ];
  }

  /**
   * Construct object from a Drupal Media object.
   *
   * @param \Drupal\media\Entity\Media $media
   *   Drupal Media element.
   * @param string $caption
   *   Media caption.
   * @param string $image_style
   *   Image style.
   * @param string $view_mode
   *   Video display view mode.
   *
   * @return static
   *   A media value object instance.
   */
  public static function fromMediaObject(Media $media, string $caption = '', $image_style = '', string $view_mode = '') {
    // Get the media source.
    $source = $media->getSource();
    $values = [
      'embedded_media' => '',
      'ratio' => '16-9',
      'image' => NULL,
      'sources' => [],
      'tracks' => [],
      'description' => '',
      'compliance' => FALSE,
    ];
    if ($source instanceof MediaAvPortalVideoSource || $source instanceof OEmbed || $source instanceof Iframe) {
      $media_type = \Drupal::service('entity_type.manager')->getStorage('media_type')->load($media->bundle());
      $values['sources']['src'] = $media->getSource();
      $values['sources']['type'] = $media_type;

      $source = $media->getSource();
      $source_field = $source->getSourceFieldDefinition($media_type);
      $display = EntityViewDisplay::collectRenderDisplay($media, $view_mode);
      $display_options = $display->getComponent($source_field->getName());
      $oembed_type = $source->getMetadata($media, 'type');
      // If it is an OEmbed resource, render it and pass it as embeddable data
      // only if it is of type video or html.
      if ($source instanceof OEmbed && in_array($oembed_type, ['video', 'html'])) {
        $values['embedded_media'] = $media->{$source_field->getName()}->view($display_options);
      }
      else {
        // If its an AvPortal video or an iframe video, render it.
        $values['embedded_media'] = $media->{$source_field->getName()}->view($display_options);

        // When dealing with iframe videos, also respect its given aspect ratio.
        if ($media->bundle() === 'video_iframe') {
          $ratio = $media->get('oe_media_iframe_ratio')->value;
          $values['ratio'] = str_replace('_', '-', $ratio);
        }
      }
      // Render the result.
      $values['embedded_media'] = \Drupal::service('renderer')->renderRoot($values['embedded_media'])->__toString();
    }
    else {
      $values['image'] = ImageValueObject::fromStyledImageItem($media->get('thumbnail')->first(), $image_style);
    }
    return new static(
      $values['embedded_media'],
      $values['ratio'],
      $values['image'],
      $values['sources'],
      $values['tracks'],
      $values['description'] = $caption,
      $values['compliance']
    );

  }

}
