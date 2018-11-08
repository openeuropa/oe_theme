# File pattern field type
This is a ui_pattern field type that can be reused across patterns. 
This field type is constructed as a value object in the background.
You can construct this value object from an Array or a Drupal file entity.

It has only the properties of a file. 
- title
- name*
- url*
- size*
- mime*
- extension
- language_code

The properties with * are required to have a value object.

Values of the properties are raw and they require formating before
rendering in preprocess hooks.


## Using file value object
