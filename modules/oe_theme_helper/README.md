# OpenEuropa Theme Helper

This module offers some additional functionality that might come in useful when
theming an OpenEuropa website.

Here is an overview of the features it offers:

## Services

### Webtools icons provider

The `webtools_icons_provider` service provides a way to retrieve icons from the webtools service.
Like this your site automatically get any new icons added by ECL and Webtools without a release of oe_theme.
There are 3 main category of icons available for now:
  - icons (generic icons)
  - flags (country flags)
  - networks (social media icons)

You can use this service to get all possible values for the specified categories.
```php
\Drupal::service('oe_theme_helper.webtools_icons_provider')->getAllowedIconValues(['icons', 'networks']);
```
Using this, you can define a custom function that can be used as an `allowed_values_function` in your field storage definition config.
```yml
settings:
  allowed_values: { ... old values ... }
  allowed_values_function: _custom_function_calling_the_webtools_icons_provider
```
For more information take a look at the [Webtools Icons Provider](modules/oe_theme_helper/src/WebtoolsIconsProviderInterface.php) interface.

## Additional Twig filters

### format_size

The `format_size` filter gives an easy way to display file sizes in human
readable format.

*Example*

If the variable `{{ filesize }}` contains the number `123456`:

```
{{ filesize|format_size }}
```

Output: `120.56 KB`.

### to_language

The `to_language` filter will convert a language code into the full language
name in the current language. Please note that for this to work the language
should be enabled, and be translated in the current language.

This filter works great in conjunction with the [OpenEuropa
Multilingual](https://github.com/openeuropa/oe_multilingual) module which
includes all translations of the European language names.

*Example*

If the variable `{{ langcode }}` contains `es` and the current language is
English:

```
{{ langcode|to_language }}
```

Output: `Spanish`.

### to_native_language

The `to_native_language` filter will convert a language code into the full language
name in the native language. Please note that for this to work the language
should be enabled, and be translated in the its own native language.

This filter works great in conjunction with the [OpenEuropa
Multilingual](https://github.com/openeuropa/oe_multilingual) module which
includes all native translations of the European language names.

*Example*

If the variable `{{ langcode }}` contains `es`:

```
{{ langcode|to_nativelanguage }}
```

Output: `Español`.

## Additional formatters

**Social media field formatter**

Provides a formatter for the social media links in order to configure horizontal or vertical variants of the pattern.
The social media field requires the [typed_link](https://www.drupal.org/project/typed_link) module.

**Field list field group formatter**

Displays field group children using the Field list pattern.
Additional formatters for other patterns can be implemented by extending the [PatternFormatterBase](modules/oe_theme_helper/src/Plugin/field_group/FieldGroupFormatter/PatternFormatterBase.php) class.

**In-page navigation field group formatter**

Display field group children using the in-page navigation ECL component. In order for this formatter to work, its children
must be field groups of type "In-page navigation item". This formatter will use its children's labels to compile the
sidebar in-page navigation list.

**Inline address formatter**

This formatter provides the possibility to configure and render address field values with
a defined separator. Default separator is ", ". Example: Rue Belliard 28, Brussels, 1000 Belgium. It works with the [address](https://www.drupal.org/project/address) module.
