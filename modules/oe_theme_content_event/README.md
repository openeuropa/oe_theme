# OpenEuropa Content Event companion module

This module is a theming companion module to the [OpenEuropa Content Event](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_event) module.
It provides the logic needed to theme the Event content type.

### Required contrib modules
This module requires the following contrib modules:
* [Extra field](https://www.drupal.org/project/extra_field) (^1.1)
* [Field group](https://www.drupal.org/project/field_group) (~3.0@rc with [this patch](https://www.drupal.org/files/issues/2019-08-22/2787179-highlight-html5-validation-40.patch))

### Shipped configurations
The modules ships configurations for several date formats that are made for
presenting event dates in different context.

List of shipped date formats:
* Event date - 27 January 2020
* Event date with hour - 27 January 2020, 11:10
* Event date with hour and timezone	- 27 January 2020, 11:10 CET
* Event long date with hour	- Monday 27 January 2020, 11:10

The [OpenEuropa Content Event](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_event) module
ships a default view mode that is overridden by this companion module to place the implemented
[Extra field](https://www.drupal.org/project/extra_field) plugin definitions.
These are a set of custom outputs with logic, that are treated like fields.
For example the event registration button has several states depending on the actual time. All these logic are
implemented into this extra field.

List of Extra field definitions:
* [Contacts](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/ContactsExtraField.php)

    Provides contact entities as renderable arrays devided into their bundles.

* [Description](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/DescriptionExtraField.php)

    Ships lazy builder callbacks and rendereable arrays with a conditional Description or Report title with their respective content.
    This section is accompanied by the featured media and it's legend. Read more about [auto-placholdering](https://www.drupal.org/docs/8/api/render-api/auto-placeholdering) with lazy builders.

* [Details](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/DetailsExtraField.php)

    Provides logic renderable arrays for event details including Topic, Location, Dates, and Livestreaming availability  information.

* [Organiser](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/OrganiserExtraField.php)

    Provides logic and a renderable array for the Organisation conditional field that can be the Organsiation text input or
    the referenced skos entity of a department (DG).

* [RegistrationButton](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/RegistrationButtonExtraField.php)

    Provides the logic for the registration button and text that is activated or dis-activated depending on the the event date is
    in the past or in the future.

* [Summary](modules/oe_theme_content_event/src/Plugin/ExtraField/Display/SummaryExtraField.php)

    Provides the logic for short summary on top of an event page that is the summary of the Description or the Report
    depending on the event date is in the past or in the future.
