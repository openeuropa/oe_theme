# OpenEuropa Content Corporate Entity Contact companion module

This module is a theming companion module to the [OpenEuropa Content Entity Contact](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_entity/modules/oe_content_entity_contact) module.

### Overridden configuration

Installing this module will override the default view modes of the following entity types:

* Contact
  * General bundle
  * Press bundle

The entity types above are shipped by the [OpenEuropa Content Entity](https://github.com/openeuropa/oe_content/tree/master/modules/oe_content_entity)
module. This is necessary in order to guarantee that fields and formatter settings are displayed correctly.

If you want to customize how those entity types look like create the `full` view mode on their bundles and take over.
