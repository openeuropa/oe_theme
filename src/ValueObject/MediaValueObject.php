<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalVideoSource;
use Drupal\oe_media_iframe\Plugin\media\Source\Iframe;
use Drupal\media\Entity\Media;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\media\Plugin\media\Source\OEmbed;

/**
 * Handle information about a media element.
 */
class MediaValueObject extends ValueObjectBase {

  /**
   * Embed video code.
   *
   * @var string
   */
  protected $video;

  /**
   * Video aspect ratio.
   *
   * @var string
   */
  protected $ratio;

  /**
   * Video aspect ratio.
   *
   * @var array
   */
  protected $sources;

  /**
   * Video aspect ratio.
   *
   * @var array
   */
  protected $tracks;

  /**
   * Image object.
   *
   * @var ImageValueObject
   */
  protected $image;

  /**
   * MediaValueObject constructor.
   *
   * @param string $video
   *   HTML code of the video to embed.
   * @param string $ratio
   *   Video aspect ratio.
   * @param array $sources
   *   Media resources for media element.
   * @param array $tracks
   *   Text tracks for video elements.
   * @param ImageValueObject $image
   *   Image value object.
   */
  private function __construct(string $video = '', string $ratio = '', array $sources = [], array $tracks = [], ImageValueObject $image = NULL) {
    $this->video = $video;
    $this->ratio = $ratio;
    $this->sources = $sources;
    $this->tracks = $tracks;
    $this->image = $image;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $values += [
      'video' => '',
      'ratio' => '',
      'sources' => [],
      'tracks' => [],
      'image' => NULL,
    ];

    return new static(
      $values['video'],
      $values['ratio'],
      $values['sources'],
      $values['tracks'],
      $values['image']
    );
  }

  /**
   * Getter.
   *
   * @return string
   *   Property value.
   */
  public function getVideo(): string {
    return $this->video;
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
   * @return ImageValueObject
   *   Property value.
   */
  public function getImage() {
    return $this->image;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'embedded_media' => $this->getVideo(),
      'ratio' => $this->getRatio(),
      'sources' => $this->getSources(),
      'tracks' => $this->getTracks(),
      'image' => $this->getImage(),
    ];
  }

  /**
   * Construct object from a Drupal Media object.
   *
   * @param \Drupal\media\Entity\Media $media
   *   Drupal Media element.
   * @param string $image_style
   *   Image style.
   * @param string $view_mode
   *   Video display view mode.
   *
   * @return static
   *   A media value object instance.
   */
  public static function fromMediaObject(Media $media, string $image_style = '', string $view_mode = '') {
    // Get the media source.
    $source = $media->getSource();
    $values = [
      'video' => '',
      'ratio' => '16-9',
      'sources' => [],
      'tracks' => [],
      'image' => NULL,
    ];
    if ($source instanceof MediaAvPortalVideoSource || $source instanceof OEmbed || $source instanceof Iframe) {
      $media_type = \Drupal::service('entity_type.manager')->getStorage('media_type')->load($media->bundle());

      $source = $media->getSource();
      $source_field = $source->getSourceFieldDefinition($media_type);
      $display = EntityViewDisplay::collectRenderDisplay($media, $view_mode);
      $display_options = $display->getComponent($source_field->getName());
      $oembed_type = $source->getMetadata($media, 'type');
      // If it is an OEmbed resource, render it and pass it as embeddable data
      // only if it is of type video or html.
      if ($source instanceof OEmbed && in_array($oembed_type, ['video', 'html'])) {
        $values['video'] = $media->{$source_field->getName()}->view($display_options);
      }
      else {
        // If its an AvPortal video or an iframe video, render it.
        $values['video'] = $media->{$source_field->getName()}->view($display_options);

        // When dealing with iframe videos, also respect its given aspect ratio.
        if ($media->bundle() === 'video_iframe') {
          $ratio = $media->get('oe_media_iframe_ratio')->value;
          $values['ratio'] = str_replace('_', '-', $ratio);
        }
      }
      // Render the result.
      $values['video'] = \Drupal::service('renderer')->renderPlain($values['video'])->__toString();
    }
    else {
      if ($image_style === '') {
        $values['image'] = ImageValueObject::fromImageItem($media->get('thumbnail')->first());
      }
      else {
        $values['image'] = ImageValueObject::fromStyledImageItem($media->get('thumbnail')->first(), $image_style);
      }
    }
    return new static(
      $values['video'],
      $values['ratio'],
      $values['sources'],
      $values['tracks'],
      $values['image']
    );

  }

}
