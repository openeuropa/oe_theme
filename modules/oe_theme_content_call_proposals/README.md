# OpenEuropa Content Call for proposals companion module

This module is a theming companion module to the [OpenEuropa Content Call for proposals](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_call_proposals) module.
It provides the logic needed to theme the Call for proposals content type.

## Installation

Make sure you have read the OpenEuropa Content Call for proposals module's [README.md](https://github.com/openeuropa/oe_content/blob/master/modules/oe_content_call_proposals/README.md) before enabling this module.

## Required contrib modules

This module requires the following contributed modules:

* [Extra field](https://www.drupal.org/project/extra_field) (^1.1)
* [Field group](https://www.drupal.org/project/field_group) (~3.0)

## Shipped configuration

The modules ships with the following configuration date formats:

List of shipped date formats:

* Call for proposals timezone date, e.g. `23 September 2020, 13:30 (CEST)`
* Call for proposals long date, e.g. `23 September 2020`

List of shipped view modes: `full` and `teaser`.

## Extra fields

This module ships with a [extra field](https://www.drupal.org/project/extra_field) plugin definition which is
used to display complex rendering business logic. All this logic is encapsulated in this extra field.

You can reuse these extra fields in your own view modes.

List of extra field definitions:

* [Status](modules/oe_content_call_proposals/src/Plugin/ExtraField/Display/CallForProposalsStatusExtraField):
  provides the call status, depending on the current time in relation with call's opening/closing dates.
* [Publication information](modules/oe_content_call_proposals/src/Plugin/ExtraField/Display/CallForProposalsPublicationInfoExtraField.php):
  provides a field to show Publication date and Publication in the official journal values on one line.
