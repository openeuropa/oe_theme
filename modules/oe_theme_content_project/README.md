# OpenEuropa Content Project companion module

This module is a theming companion module to the [OpenEuropa Content Project](https://github.com/openeuropa/oe_content/tree/EPIC-Project/modules/oe_content_project) module.
It provides the logic needed to theme the Project content type.

## Installation

Make sure you you have read the OpenEuropa Content Project module's [README.md](https://github.com/openeuropa/oe_content/blob/EPIC-Project/modules/oe_content_project/README.md)
before enabling this module.

After enabling this module make sure you assign the following permissions to the anonymous user role, so visitors can
correctly access all project information.

- `Organisation: View any published entity`

## Required contrib modules

This module requires the following contrib modules:

* [Extra field](https://www.drupal.org/project/extra_field) (^1.1)
* [Field group](https://www.drupal.org/project/field_group) (~3.0)

## Overridden configuration

Installing this module will override the default project content type view mode, shipped by the
[OpenEuropa Content Project](https://github.com/openeuropa/oe_content/tree/EPIC-Project/modules/oe_content_project)
module. This is necessary in order to guarantee that fields and formatter settings are displayed correctly.

If you want to customize how the project looks like create the `full` view mode and take over.

## Extra fields

This module ships with a [extra field](https://www.drupal.org/project/extra_field) plugin definition which is
used to display complex rendering business logic. All this logic is encapsulated in this extra field.

You can reuse these extra fields in your own view modes.

List of Extra field definitions:

* [Percentage](modules/oe_theme_content_project/src/Plugin/ExtraField/Display/PercentageExtraField.php) provides a field that
calculates the eu percentage of the project budget.
