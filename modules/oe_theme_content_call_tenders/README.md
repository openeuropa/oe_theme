# OpenEuropa Content Call for tenders companion module

This module is a theming companion module to the [OpenEuropa Content Call for tenders](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_call_tenders) module.
It provides the logic needed to theme the Call for tenders content type.

## Installation

Make sure you have read the OpenEuropa Content Call for tenders module's [README.md](https://github.com/openeuropa/oe_content/blob/master/modules/oe_content_call_tenders/README.md)
before enabling this module.

## Required contrib modules

This module requires the following contributed modules:

* [Extra field](https://www.drupal.org/project/extra_field) (^1.1)
* [Field group](https://www.drupal.org/project/field_group) (~3.0)

## Shipped configuration

The modules ships with the following configuration date formats:

List of shipped date formats:

* Call for tenders timezone date, e.g. `23 September 2020, 13:30 (CEST)`
* Call for tenders long date, e.g. `23 September 2020`

## Overridden configuration

Installing this module will override the default "Call for tenders" content type view mode, shipped by the
[OpenEuropa Content Call for tenders](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_call_tenders)
module. This is necessary in order to guarantee that fields and formatter settings are displayed correctly.

If you want to customize how the "Call for tenders" looks like create the `full` view mode and take over.

This modules also ships with a `teaser` view mode.

## Extra fields

This module ships with a [extra field](https://www.drupal.org/project/extra_field) plugin definition which is
used to display complex rendering business logic. All this logic is encapsulated in this extra field.

You can reuse these extra fields in your own view modes.

List of Extra field definitions:

* [Call for tender status](modules/oe_content_call_tenders/src/Plugin/ExtraField/Display/CallForTendersStatusExtraField.php):
  provides the call status, depending on the current time in relation with call's opening/closing dates.
* [Call for tender status label](modules/oe_content_call_tenders/src/Plugin/ExtraField/Display/CallForTendersLabelStatusExtraField.php):
  same as above, only showed using ECL label component, and prefixed by "Call status:".
