# Developer documentation

The OpenEuropa Theme exposes ECL components using [UI Patterns][1] plugins, allowing them to be used seamlessly as drop-in
templates for panels, field groups, views, paragraphs, nodes, etc.

Patterns are located in [`./templates/patterns`](../templates/patterns), each sub-directory contains a pattern along with
its variants. Pattern definitions are stored as YAML files (for ex. `[PATTERN-NAME].ui_patterns.yml`) along with their
Twig templates (for ex. `pattern-[PATTERN-NAME].html.twig`).

Each pattern definition exposes a list of fields that can be used to pass data that will be rendered using the
related pattern template.

Pattern fields can accept either one of the following types:

- A renderable markup, i.e. a Drupal render array or a string
- A value object or a list of value objects

## Using value objects

The scope of value objects is to make sure that data is passed to the final templates in a consistent and predictable way.
Value objects are available at [`./src/ValueObject`](../src/ValueObject) and implement `\Drupal\oe_theme\ValueObject\ValueObjectInterface`.

Value objects can be constructed only by using one of their factory methods. By default all value objects expose the
`ValueObjectInterface::fromArray()` factory which builds and returns a value object from a given array.

When patterns are rendered programmatically, value objects can be passed directly to related pattern fields, as shown below:

```php
<?php

use Drupal\oe_theme\ValueObject\FooValueObject;

$elements['quote'] = [
  '#type' => 'pattern',
  '#id' => 'my_pattern',
  '#fields' => [
    'foo' => FooValueObject::fromArray(['bar' => 'Bar']),
  ]
];

\Drupal::service('renderer')->render($elements);

```

Value objects will typically be constructed in template preprocess functions, for example when rendering a pattern
preview you would have:

```php
<?php

use Drupal\oe_theme\ValueObject\FooValueObject;

/**
 * Implements hook_preprocess_pattern_MY_PATTERN().
 */
function MY_MODULE_preprocess_pattern_MY_PATTERN(&$variables) {
  $variables['foo'] = FooValueObject::fromArray($variables['foo']);
}
```

Or when using a pattern to render an Article teaser view mode you would have:

```php
<?php

use Drupal\oe_theme\ValueObject\FooValueObject;

/**
 * Implements hook_preprocess_pattern_MY_PATTERN().
 */
function MY_MODULE_preprocess_node__article__teaser(&$variables) {
  $variables['foo'] = FooValueObject::fromNodeEntity($variables['node']);
}
```

And in your  `node--article--teaser.html.twig` template:

```twig
{{ pattern('my_pattern', { foo: foo.getArray() }) }}
```

Check the [UI Patterns documentation][2] for more information about how to use patterns in your project.

### Available value objects

Below a list of available value object along with its factory methods:

#### `DateValueObject`

Used in the following patterns:

- [`date_block`](../templates/patterns/date_block/date_block.ui_patterns.yml)

Provides the following factory methods:

- `DateValueObject::fromArray(array $values = [])`: accepts an array with the following properties:
  - `start`: Start date as UNIX timestamp.
  - `end`: End date as UNIX timestamp.
  - `timezone`: Timezone string, e.g. "Europe/Brussels".

- `DateValueObject::fromTimestamp(int $start, int $end = NULL, string $timezone = NULL)`:
  - `$start`: Start date as UNIX timestamp.
  - `$end`: End date as UNIX timestamp.
  - `$timezone`: Timezone string, e.g. "Europe/Brussels".

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

- `FileValueObject::fromFileEntity(FileInterface $file_entity)`: accepts an object implementing the
  `\Drupal\file\FileInterface` interface.

#### `GalleryItemValueObject`

Used in the following patterns:

- [`gallery`](../templates/patterns/gallery/gallery.ui_patterns.yml)

Provides the following factory methods:

- `GalleryItemValueObject::fromArray(array $values = [])`: accepts an array with the following properties:
  - `thumbnail`: Thumbnail to be rendered on the gallery item. Check ImageValueObject::fromArray(array $values = []) for a list of acceptable array values.
  - `caption`: Caption for the gallery item.
  - `classes`: Extra classes for the gallery item.
  - `icon`: Icon for the gallery item.

#### `ImageValueObject`

Used in the following patterns:

- [`gallery`](../templates/patterns/gallery/gallery.ui_patterns.yml)

Provides the following factory methods:

- `ImageValueObject::fromArray(array $values = [])`: accepts an array with the following properties:
  - `src`: Image URL, including Drupal schema if internal.
  - `alt`: Image alt text.
  - `name`: Name of the image, e.g. "example.jpg".
  - `responsive`: Responsiveness of the image. Optional, set to TRUE be default.

- `ImageValueObject::fromImageItem(ImageItem $image_item)`:  accepts a \Drupal\image\Plugin\Field\FieldType\ImageItem object.

[1]: https://www.drupal.org/project/ui_patterns
[2]: https://ui-patterns.readthedocs.io
