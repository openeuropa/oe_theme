# OpenEuropa Theme Organisation Content module

This module is a theming companion module to the [OpenEuropa Organisation Content](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_organisation) module.
It provides the logic needed to theme the Organisation content type.

## Installation

Make sure you have read the OpenEuropa Content Organisation module's [README.md](https://github.com/openeuropa/oe_content/blob/master/modules/oe_content_organisation/README.md)
before enabling this module.

After enabling this module make sure you assign the following permissions to the anonymous user role, so visitors can
correctly access all organisation information.

- `Contact: View any published entity`

## Overridden configuration

Installing this module will override the default organisation content type view mode, shipped by the
[OpenEuropa Organisation Content](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_organisation)
module. This is necessary in order to guarantee that fields and formatter settings are displayed correctly.

If you want to customize how the organisation looks like create the `full` view mode and take over.
