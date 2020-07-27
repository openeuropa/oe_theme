<?php

/**
 * @file
 * Preprocessors for field_group_in_page_navigation elements.
 */

declare(strict_types = 1);

/**
 * Prepares variables for in-page navigation..
 *
 * Default template: oe-theme-helper-in-page-navigation.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties and children of
 *     the in-page navigation element. Properties used: #children.
 */
function template_preprocess_field_group_in_page_navigation(array &$variables) {
  $element = $variables['element'];
  $variables['children'] = (!empty($element['#children'])) ? $element['#children'] : '';
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * Adds field template suggestion to all in-page-navigation fields.
 */
function oe_theme_helper_theme_suggestions_field_alter(array &$suggestions, array $variables): void {
  $element = $variables['element'];

  if (!empty($variables['element']['#in_page_navigation'])) {
    $suggestions[] = 'field__in_page_navigation';
    $suggestions[] = 'field__' . $element['#field_type'] . '__in_page_navigation';
    $suggestions[] = 'field__' . $element['#field_name'] . '__in_page_navigation';
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#bundle'] . '__in_page_navigation';
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#field_name'] . '__in_page_navigation';
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#field_name'] . '__' . $element['#bundle'] . '__in_page_navigation';
  }
}
