# Innovo Cache Improvements

A community extension that increases your Magento 2 store performance.

It persists server side 'full page' cache of category pages until a product goes "out of stock" after customer checkout. Additionally the extension improves the default top menu block by letting it have only one cache version on all pages (where is displayed).

Thank you for using Innovo Cache Improvements.

## List of features
* Full page cache policy improvement on products still in stock after checkout.
* Full page cache policy improvement on products still in stock after an order is created from admin.
* Top menu policy block cache improvement.
* Ability to change from admin the cache lifetime of top menu block.
* Ability to disabled ESI policy on top menu block or change it's TTL. ESI policies are used by Varnish.
* Active/current category highlight is kept on Magento's default implementation of top menu.
* Each feature of this extension can be enabled or safely disabled from admin configurations.
* Compatible with Magento's built-in full page cache and Varnish full page cache.
* Compatible with 'Update on Save' and 'Update by Schedule' indexer modes.
* Admin configuration to allow cache cleaning on specific quantitiy numbers that belong to intervals of numbers. e.g Clean cache when quantity is lower than 5.
* Admin configuration to allow cache cleaning on quantity numbers divided by a number. eg. Clean cache every time the quantity can divide by 10.

## Instalation

### With composer:

Add composer repository
```
composer config repositories.innovo-cache-improve git "https://github.com/daniel-ifrim/innovo-cache-improve.git"
```

Get the code
```
composer require innovo/module-cache-improve
```

And please continue to install the extenision like any other Magento 2 module.

### Download archive

Unpack the contents of the archive and copy the contents of dir innovo-cache-improve-master into
```
app/code/Innovo/CacheImprove
```

And please continue to install the extenision like any other Magento 2 module.

## Configuration

Please see https://marketplace.magento.com/innovo-module-cache-improve.html

## Support

* bug
* new features
* upgrades to newer Magento 2 versions
* possibly compatibility with 3rd party Magento 2 extensions / modules

## License

The Open Software License 3.0 (OSL-3.0)
