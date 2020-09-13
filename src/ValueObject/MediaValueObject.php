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
   * Video aspect ratio allowed values.
   *
   * @var array
   */
  protected static $allowedValues = ['16-9', '4-3', '3-2', '1-1'];

  /**
   * Video aspect default ratio.
   *
   * @var string
   */
  protected static $defaultRatio = '16-9';

  /**
   * Media resources.
   *
   * @var array
   */
  protected $sources;

  /**
   * Tracks for videos.
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
    $ratio = isset($values['ratio']) ? self::validateRatio($values['ratio']) : self::validateRatio('');
    $values += [
      'video' => '',
      'sources' => [],
      'tracks' => [],
      'image' => NULL,
    ];

    return new static(
      $values['video'],
      $values['ratio'] = $ratio,
      $values['sources'],
      $values['tracks'],
      $values['image']
    );
  }

  /**
   * Get the video attribute value.
   *
   * @return string
   *   The video attribute value.
   */
  public function getVideo(): string {
    return $this->video;
  }

  /**
   * Get the ratio attribute value.
   *
   * @return string
   *   Video ratio.
   */
  public function getRatio(): string {
    return $this->ratio;
  }

  /**
   * Get the sources attribute value.
   *
   * @return array
   *   The video sources.
   */
  public function getSources(): array {
    return $this->sources;
  }

  /**
   * Get the tracks attribute value.
   *
   * @return array
   *   Video tracks.
   */
  public function getTracks(): array {
    return $this->tracks;
  }

  /**
   * Get the image attribute value.
   *
   * @return ImageValueObject|null
   *   The image object or null.
   */
  public function getImage(): ?ImageValueObject {
    return $this->image;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'video' => $this->getVideo(),
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
  public static function fromMediaObject(Media $media, string $image_style = '', string $view_mode = ''): ValueObjectInterface {
    // Get the media source.
    $source = $media->getSource();
    $values = [
      'video' => '',
      'ratio' => '',
      'sources' => [],
      'tracks' => [],
      'image' => NULL,
    ];

    if ($source instanceof MediaAvPortalVideoSource || $source instanceof OEmbed || $source instanceof Iframe) {
      $media_type = \Drupal::service('entity_type.manager')->getStorage('media_type')->load($media->bundle());
      $source_field = $source->getSourceFieldDefinition($media_type);
      $display = EntityViewDisplay::collectRenderDisplay($media, $view_mode);
      $display_options = $display->getComponent($source_field->getName());
      $values['video'] = $media->{$source_field->getName()}->view($display_options);

      // When dealing with iframe videos, also respect its given aspect ratio.
      if ($media->bundle() === 'video_iframe') {
        $ratio = $media->get('oe_media_iframe_ratio')->value;
        $values['ratio'] = str_replace('_', '-', $ratio);
      }
      $values['ratio'] = isset($values['ratio']) ? self::validateRatio($values['ratio']) : self::validateRatio('');
      // Render the result.
      $values['video'] = \Drupal::service('renderer')->renderPlain($values['video'])->__toString();

      return new static(
        $values['video'],
        $values['ratio'],
        $values['sources'],
        $values['tracks'],
        $values['image']
      );
    }

    if ($image_style === '') {
      $values['image'] = ImageValueObject::fromImageItem($media->get('thumbnail')->first());
    }
    else {
      $values['image'] = ImageValueObject::fromStyledImageItem($media->get('thumbnail')->first(), $image_style);
    }

    return new static(
      $values['video'],
      $values['ratio'],
      $values['sources'],
      $values['tracks'],
      $values['image']
    );
  }

  /**
   * Validate and transform aspect ratio.
   *
   * @param string $ratio
   *   The video ratio to validate.
   *
   * @return string
   *   The transformed video ratio.
   */
  public static function validateRatio(string $ratio) {
    $ratio = str_replace('_', '-', $ratio);
    $ratio = str_replace(':', '-', $ratio);
    if (!in_array($ratio, self::$allowedValues)) {
      $ratio = self::$defaultRatio;
    }
    return $ratio;
  }

}
