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
class MediaValueObject extends ValueObjectBase implements MediaValueObjectInterface {

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
   * @var ImageValueObjectInterface
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
   * @param ImageValueObjectInterface|null $image
   *   Image value object.
   */
  private function __construct(string $video = '', string $ratio = '', array $sources = [], array $tracks = [], ImageValueObjectInterface $image = NULL) {
    $this->video = $video;
    $this->ratio = $ratio;
    $this->sources = $sources;
    $this->tracks = $tracks;
    $this->image = $image;
  }

  /**
   * {@inheritdoc}
   */
  public function getVideo(): string {
    return $this->video;
  }

  /**
   * {@inheritdoc}
   */
  public function getRatio(): string {
    return $this->ratio;
  }

  /**
   * {@inheritdoc}
   */
  public function getSources(): array {
    return $this->sources;
  }

  /**
   * {@inheritdoc}
   */
  public function getTracks(): array {
    return $this->tracks;
  }

  /**
   * {@inheritdoc}
   */
  public function getImage(): ?ImageValueObjectInterface {
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

    $values['ratio'] = self::validateRatio($values['ratio']);

    return new static(
      $values['video'],
      $values['ratio'],
      $values['sources'],
      $values['tracks'],
      $values['image']
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function fromMediaObject(Media $media, string $image_style = '', string $view_mode = ''): MediaValueObjectInterface {
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

      // When dealing with iframe videos, also respect it's given aspect ratio.
      if ($media->bundle() === 'video_iframe') {
        $values['ratio'] = $media->get('oe_media_iframe_ratio')->value;
      }

      $values['ratio'] = self::validateRatio($values['ratio']);
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
   * {@inheritdoc}
   */
  public static function validateRatio(string $ratio): string {
    $ratio = str_replace(['_', ':'], '-', $ratio);

    if (!in_array($ratio, MediaValueObjectInterface::ALLOWED_VALUES)) {
      $ratio = MediaValueObjectInterface::DEFAULT_RATIO;
    }

    return $ratio;
  }

}
