<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides a trusted callback to alter the content language switcher.
 *
 * @see oe_theme_helper_block_view_oe_theme_helper_page_header_alter()
 */
class LanguageSwitcherBuilderCallback implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['preRender'];
  }

  /**
   * Pre-render callback for the Page Header block alteration.
   *
   * We use this to add the language switcher
   * to the page header if OpenEuropa Theme is being used.
   *
   * @param array $build
   *   The built render array of the block.
   *
   * @see \Drupal\oe_theme_helper\Plugin\Block\PageHeaderBlock
   *
   * @return array
   *   The built render array of the block.
   */
  public static function preRender(array $build): array {
    // Get required services.
    $multilingual_helper = \Drupal::service('oe_multilingual.helper');
    $content_language_switcher_provider = \Drupal::service('oe_multilingual.content_language_switcher_provider');
    $language_manager = \Drupal::languageManager();
    $cache = CacheableMetadata::createFromRenderArray($build);
    $cache->addCacheContexts(['languages:language_content']);

    $entity = $multilingual_helper->getEntityFromCurrentRoute();
    // Bail out if there is no entity or if it's not a content entity.
    if (!$entity || !$entity instanceof ContentEntityInterface) {
      $cache->applyTo($build);
      return $build;
    }

    $cache->addCacheableDependency($entity);
    $cache->applyTo($build);

    // Render the links only if the current entity translation language is not
    // the same as the current site language.
    /** @var \Drupal\Core\Entity\EntityInterface $translation */
    $translation = $multilingual_helper->getCurrentLanguageEntityTranslation($entity);
    $current_language = $language_manager->getCurrentLanguage();
    if ($translation->language()->getId() === $current_language->getId()) {
      return $build;
    }

    $content = &$build['content'];

    $content['#language_switcher']['current'] = $translation->language()->getName();

    /** @var \Drupal\Core\Language\LanguageInterface[] $languages */
    $languages = $language_manager->getNativeLanguages();
    $content['#language_switcher']['unavailable'] = $languages[$current_language->getId()]->getName();

    // Normalize the links to an array of options suitable for the ECL
    // "ecl-lang-select-pages" template.
    $content['#language_switcher']['options'] = [];
    foreach ($content_language_switcher_provider->getAvailableEntityLanguages($entity) as $language_code => $link) {
      /** @var \Drupal\Core\Url $url */
      $url = $link['url'];
      $href = $url
        ->setOptions(['language' => $link['language']])
        ->setAbsolute(TRUE)
        ->toString();

      $content['#language_switcher']['options'][] = [
        'href' => $href,
        'hreflang' => $language_code,
        'label' => $link['title'],
        'lang' => $language_code,
      ];
    }

    $content['#language_switcher']['is_primary'] = TRUE;

    return $build;
  }

}
