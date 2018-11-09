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

Value objects can be constructed only by using one of their factory methods. By default all value objects will expose the
following factory:

- `ValueObjectInterface::fromArray()`: build and return a value object from a given array.

For cases like component preview the Value objects must be constructed in pattern template preprocess functions, because 
you can only pass the array from the YAML configuration.

Example how to use the value object:

```php
<?php

use Drupal\oe_theme\ValueObject\FooValueObject;

/**
 * Implements hook_preprocess_pattern_MY_PATTERN().
 */
function oe_theme_preprocess_pattern_MY_PATTERN(&$variables) {
  $variables['foo'] = FooValueObject::fromArray($variables['foo']);
}
```

### Available value objects

Below a list of available value object along with its factory methods:

#### `FileValueObject`

Used in the following patterns:

- [`file`](../templates/patterns/file/file.ui_patterns.yml)
- [`file_link`](../templates/patterns/file_link/file_link.ui_patterns.yml)
- [`file_translation`](../templates/patterns/file_translation/file_translation.ui_patterns.yml)

Provides the following factory methods:

- `FileValueObject::fromArray(array $values = [])`: accepts an array with the following properties:
  - `name`: file name, e.g. `my-document.pdf`
  - `url`: file URL, it can accept Drupal file URIs as well.
  - `mime`: file MIME type.
  - `size`: file size in bytes.
  - `title` (optional): file title, defaults to file `name` if empty.
  - `language_code` (optional): two letter language code of the current file's language.
- `FileValueObject::fromFileEntity(FileInterface $file_entity)`: accept an object implementing the
  `\Drupal\file\FileInterface` interface.
