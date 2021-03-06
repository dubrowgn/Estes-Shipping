# Wp-e-Commerce Estes Shipping

Author: Dustin Brown  
Tags: e-commerce, shipping, estes  
Requires at least: 3.6.1 
Tested up to: 3.9.1  
Stable tag: 1.2.7  

Estes less-than-load freight module for the WP e-Commerce plugin

## Description

A custom shipping module for the Wp-e-commerce plugin. It allows
shipping via Estes less-than-truckload freight. A checkbox is added
to product edit pages, under an "Estes Shipping Settings" metadata
box. Checking this box flags the product as must be shipped via Estes
LTL only. Other shipping options will not be available to the user if
the cart contains *any* LTL items. Conversely, Estes shipping will
not be available as an option if the cart contrians *no* LTL items.

## License

[GPLv3](http://www.gnu.org/licenses/)

## Installation

The following plugin *must* already be installed:

[WP e-Commerce] (http://wordpress.org/extend/plugins/wp-e-commerce/)

## Changelog

1.2.7 (May 13, 2014)
 * Additional compatability fix for WP e-Commerce 3.8.14.1

1.2.6 (May 13, 2014)
 * Compatability fix for WP e-Commerce 3.8.14.1

1.2.5 (Feb 19, 2014)
 * Added user selectable commercial/residential drop down box

1.1.4 (Oct 30, 2013)
 * Fixed isLTL writing to the wrong metadata field for products without variations

1.1.3 (Oct 20, 2013)
 * Added isLTL setting to variations metabox. IsLTL can now only be set on products without variations.
 * Product metadata related to Estes shipping has been moved under '_wpsc_product_metadata'

1.0.2 (Aug 13, 2013)
 * Added support for error messages on the checkout page
 * International shipping changes
 * Formatting and escape character fixes

1.0.1 (Aug 7, 2013)
 * Initial Release
