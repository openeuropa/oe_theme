<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_project\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Project content entity.
 *
 * @PageHeaderMetadata(
 *   id = "project_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Project content type"),
 *   weight = -1
 * )
 */
class ProjectContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_project';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();
    $metadata['metas'] = [
      $this->t('Project'),
    ];
    return $metadata;
  }

}
