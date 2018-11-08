# Developer documentation

The OpenEuropa Theme exposes ECL components as [UI Patterns][1] plugins, allowing them to be used seamlessly as drop-in
templates for panels, field groups, views, paragraphs, nodes, etc.

Patterns are located in [`./templates/patterns`](../templates/patterns), each sub-directory contains a pattern along with
its variants. Pattern definitions are stored in a YAML file (`[PATTERN-NAME].ui_patterns.yml`) along with their Twig templates.

Each pattern definition exposes a list of fields that can be used to pass data to be rendered using the pattern template.

Pattern fields can accept either one of the following types:

- A renderable markup, i.e. a Drupal render array or a string
- A value object or a list of value objects

The scope of value objects is to make sure that data is passed to the final templates in a consistent and predictable way.
Value objects are available at [`./src/ValueObject`](../src/ValueObject) and implement the `\Drupal\oe_theme\ValueObject\ValueObjectInterface`.

## Using value objects

Value objects can be constructed only by using one or more factory methods. By default all value objects will expose the
following factories:

- `ValueObjectInterface::fromArray()`: build and return a value object from a given array.
- `ValueObjectInterface::fromAny()`: build and return a value object from any value. This factory will be calling other
  factories, such as `ValueObjectInterface::fromArray()` or anything more specific.

Value objects must be generally constructed in template preprocess functions, like the example below:

```php
<?php

/**
 * Implements hook_preprocess_pattern_file().
 */
function oe_theme_preprocess_pattern_file(&$variables) {
  if ($variables['file']) {
    try {
      /** @var \Drupal\oe_theme\ValueObject\FileValueObject $file */
      $file = FileValueObject::fromAny($variables['file']);
      $variables['file'] = _oe_theme_get_formatted_file_type_values($file);
    }
    catch (ValueObjectException $e) {
      \Drupal::logger('oe_theme')->error('Could not set "file" field on "file" pattern: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }
}
```
