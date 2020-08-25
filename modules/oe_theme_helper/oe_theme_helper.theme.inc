<?php

/**
 * @file
 * Preprocessors and theme functions of OpenEuropa Theme Helper module.
 */

declare(strict_types = 1);

/**
 * Prepares variables for in-page navigation.
 *
 * Default template: oe-theme-helper-in-page-navigation.html.twig.
 *
 * @param array $variables
 *   An array of variables.
 */
function template_preprocess_oe_theme_helper_in_page_navigation(array &$variables) {
  $element = $variables['element'];

  $variables['title'] = !empty($element['#title']) ? $element['#title'] : '';
  $variables['children'] = !empty($element['#children']) ? $element['#children'] : '';
  $variables['links'] = !empty($element['#links']) ? $element['#links'] : '';
}

/**
 * Prepares variables for in-page navigation item.
 *
 * Default template: oe-theme-helper-in-page-navigation-item.html.twig.
 *
 * @param array $variables
 *   An array of variables.
 */
function template_preprocess_oe_theme_helper_in_page_navigation_item(array &$variables) {
  $element = $variables['element'];

  $variables['title'] = !empty($element['#title']) ? $element['#title'] : '';
  $variables['children'] = !empty($element['#children']) ? $element['#children'] : '';
  $variables['title_attributes'] = !empty($element['#title_attributes']) ? $element['#title_attributes'] : '';
}

/**
 * Default theme suggestions for custom field groups.
 *
 * @param array $variables
 *   An array of variables passed to the theme hook.
 * @param string $hook
 *   Hook name.
 *
 * @return array
 *   Theme suggestions.
 */
function oe_theme_helper_field_group_theme_suggestions(array $variables, string $hook): array {
  $element = $variables['element'];
  $suggestions = [];

  $name = !empty($element['#group_name']) ? $element['#group_name'] : NULL;
  $entity_type = !empty($element['#entity_type']) ? $element['#entity_type'] : NULL;
  $bundle = !empty($element['#bundle']) ? $element['#bundle'] : NULL;

  if (!empty($name)) {
    $suggestions[] = $hook . '__' . $name;

    if (!empty($entity_type)) {
      $suggestions[] = $hook . '__' . $entity_type . '__' . $name;
    }
  }

  if (!empty($entity_type) && !empty($bundle)) {
    $suggestions[] = $hook . '__' . $entity_type . '__' . $bundle;

    if (!empty($name)) {
      $suggestions[] = $hook . '__' . $entity_type . '__' . $bundle . '__' . $name;
    }
  }

  return $suggestions;
}

/**
 * Implements hook_theme_suggestions_HOOK().
 */
function oe_theme_helper_theme_suggestions_oe_theme_helper_in_page_navigation(array $variables) {
  return oe_theme_helper_field_group_theme_suggestions($variables, 'oe_theme_helper_in_page_navigation');
}

/**
 * Implements hook_theme_suggestions_HOOK().
 */
function oe_theme_helper_theme_suggestions_oe_theme_helper_in_page_navigation_item(array $variables) {
  return oe_theme_helper_field_group_theme_suggestions($variables, 'oe_theme_helper_in_page_navigation_item');
}
