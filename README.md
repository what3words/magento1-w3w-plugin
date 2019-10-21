What3Words Magento1 Integration

## Installation

To install follow the instructions on https://developer.what3words.com/tools/e-commerce/magento1. 

The Magento 1 Extension can be installed using Packagist or alternatively installed manually by downloading from Github and adding to your server via FTP or by performing a manual extension installation.

We recommend using Composer and Packagist to install the Extension to ensure that all dependencies are installed along with the Extension.

To add the what3words Magento 1 Extension to a store using Packagist, first connect to your server and start the command line interface.

From the CLI run the command 'composer require what3words/module-what3words-magento1' to install the files and dependencies required by the extension.

Next, run 'php bin/magento setup:upgrade' to compile the site to ensure it includes the new extension
We then recommend running 'php bin/magento cache:clean' and 'php bin/magento cache:flush' to ensure the site’s cache is cleared.

## Overview

The what3words Magento integration (developed by Gene Commerce http://gene.co.uk/) allows Magento merchants to add an option to their checkout that will let customers use a what3words address for shipping.

When enabled, the module adds a configurable and customisable what3words input field to both the billing and shipping address forms in the checkout. This lets customers save a what3words address to their address book to be easily reused in future orders.

The extension implements the official what3words jQuery plugin (https://github.com/what3words/jquery-plugin-w3w-autosuggest).


## Configuration
Enable in the what3words section of the system config and add your API key,

To display on shipping labels, please update the following config setting:
> Customers -> Customer Configuration -> Address Templates -> PDF to the following:

``{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}|
{{depend company}}{{var company}}|{{/depend}}
{{if street1}}{{var street1}}
{{/if}}
{{depend street2}}{{var street2}}|{{/depend}}
{{depend street3}}{{var street3}}|{{/depend}}
{{depend street4}}{{var street4}}|{{/depend}}
{{if city}}{{var city}},|{{/if}}
{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}|
{{var country}}|{{if w3w}}{{var w3w}}, {{/if}}|
{{depend telephone}}T: {{var telephone}}{{/depend}}|
{{depend fax}}<br/>F: {{var fax}}{{/depend}}|
{{depend vat_id}}<br/>VAT: {{var vat_id}}{{/depend}}|``

## Data flow
The system handles the 3 word address in the following steps:

1. From the billing / shipping address save actions, an Observer (`What3Words_What3Words_Model_Observer`) saves the 3 word address to
the `sales_quote_w3w` table.

See `What3Words/What3Words/etc/config.xml` for exact events being observed.

2. If the customer selects the 'Save in Address Book' option, the 3 word address is also recorded in the `customer_address_w3w` table and as well as being saved
to the customer address attribute 'w3w'.

3. The same observer class watches the `sales_order_place_after` event and saves the 3 word address to `sales_order_w3w`, after retrieving this
from the `sales_quote_w3w` table.
## Modification with custom checkouts

The 
```fields.js``` file appends the html scripts in 

```frontend/base/default/template/what3words/checkout```

to the billing and shipping forms in the

```initBillingField``` and ```initShippingField``` methods.

These can be adapted to work with whatever markup has been changed, by altering the jQuery selectors. 
