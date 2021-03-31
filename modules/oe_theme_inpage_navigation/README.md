# OpenEuropa Theme In-Page Naviation

This module provides In-page navigation capability to your content types.

## Installation

To install the in-page navigation plugin on a given content type, you need to
run once the following:

```
$bundle = 'your_content_type';
/** @var \Drupal\emr\EntityMetaRelationInstaller $installer */
$installer = \Drupal::service('emr.installer');
$installer->installEntityMetaTypeOnContentEntityType('oe_theme_inpage_navigation', 'node', [$bundle]);
```

## Requirements

This module requires Entity Meta Relation which you can install like so:

```bash
$ composer require drupal/emr
```
