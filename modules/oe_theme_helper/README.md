# OpenEuropa Theme Helper

This module offers some additional functionality that might come in useful when
theming an OpenEuropa website.

Here is an overview of the features it offers:

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

It provides formatter for the social media links in order to configure horizontal or vertical variants of the pattern.
Social media field requires [typed_link](https://www.drupal.org/project/typed_link) module.

**Field group formatter**

This formatter is made to format the fields - that are organised under a certain field group - with the Field list pattern.
Additional formatters for other patterns can be implemented by extending the [PatternFormatterBase](modules/oe_theme_helper/src/Plugin/field_group/FieldGroupFormatter/PatternFormatterBase.php) class.

**Inline address formatter**

This formatter provides the possibility to configure and render address field values with
a defined separator. Default separator is ", ". Example: Rue Belliard 28, Brussels, 1000 Belgium. It works with
[address](https://www.drupal.org/project/address) module.
