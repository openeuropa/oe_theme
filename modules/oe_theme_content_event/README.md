# OpenEuropa Content Event companion module

This module is a theming companion module to the [OpenEuropa Content Event](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_event) module.
It provides the logic needed to theme the Event content type.

### Required contrib modules

This module requires the following contrib modules:

* [Extra field](https://www.drupal.org/project/extra_field) (^1.1)
* [Field group](https://www.drupal.org/project/field_group) (~3.0)

### Shipped configuration

The modules ships configurations for several date formats that are made for presenting event dates in different context.

List of shipped date formats:

* Event date, e.g. `27 January 2020`
* Event date with hour, e.g. `27 January 2020, 11:10`
* Event date with hour and timezone, e.g. `27 January 2020, 11:10 CET`
* Event long date with hour, e.g. `Monday 27 January 2020, 11:10`

### Overridden configuration

Installing this module will override the default event content type view mode, shipped by the
[OpenEuropa Content Event](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_event)
module. This is necessary in order to guarantee that fields and formatter settings are displayed correctly.

If you want to customize how the event looks like create the `full` view mode and take over.  

### Extra fields

This module ships with a set of [extra field](https://www.drupal.org/project/extra_field) plugin definitions which are
used to display complex rendering business logic. For example, the event registration button has several states
depending on the current time. All this logic is encapsulated in this extra field.

You can reuse these extra fields in your own view modes.

List of Extra field definitions:

* [Contacts](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/ContactsExtraField.php) provides Contact
  entities as renderable arrays divided by their bundles.
* [Description](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/DescriptionExtraField.php) conditionally
  renders the Description or Report title, with their respective content. This section is accompanied by the featured media and its legend.
* [Details](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/DetailsExtraField.php) provides event details
  including Topic, Location, Dates, and Livestreaming availability information.
* [Organiser](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/OrganiserExtraField.php) provides
  organiser information, whether a simple organiser name or a label to a department OP (SKOS vocabulary).
* [RegistrationButton](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/RegistrationButtonExtraField.php)
  provides the registration button and related text, changing depending on the event date.
* [Summary](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/SummaryExtraField.php) provides the event
  short summary or the report short summary, depending on whether the event date is in the past or in the future.
