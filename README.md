# RevolutionCache

A PSR-6 cache implementation to access the MODX Revolution Cache Provider.

This can be used to write custom application code against the PSR-6 caching standard, while integrating with the standard MODX 2.2+ Cache Manager. This offers access to the different cache implementations provided by MODX (file/memcached and others), and partition configuration through MODX System Settings.

RevolutionCache was developed for [Commerce](https://www.modmore.com/commerce/), but extracted into a standalone composer package to benefit others. 

## Installation

Install RevolutionCache via [Composer](https://getcomposer.org):

````
composer require modmore/revolution-cache
````

## License

MIT

## Usage

You can [read more about the PSR-6 Caching Interface Standard here](http://www.php-fig.org/psr/psr-6/).

A simple example of how you might use this adapter is as follows.

```` php
require_once '/path/to/vendor/autoload.php';

$pool = new \modmore\RevolutionCache\Pool($modx);

// Do some cache stuff
$item = $pool->getItem('my_value');
if ($item->isHit()) {
    echo 'Your number is ' . $item->get() . ' and was loaded from cache.';
}
else {
    $value = mt_rand(1, 9999);
    $item->set($value);
    if ($pool->save($item)) {
        echo 'Your number is ' . $value . ' and has been stored in the cache.';
    }
    else {
        echo 'Your number is ' . $value . ' but could not be written to cache';
    }
}
````

To use a partition other than `default`, you can specify it as the second option when creating the `$pool` instance:

```` php
$pool = new \modmore\RevolutionCache\Pool($modx, 'my_custom_partition');
````

The third `$options` property on the constructor can be used to specify xPDOCache options like the default expiry date, cache handler, and cache format, however it's recommended to configure this via MODX System Settings instead.

## Notes

- This caching implementation does not support deferred saving: `$pool->saveDeferred()` is identical to `$pool->save()` and `$pool->commit()` is always true. 
- This implementation does **not** support extensions to PSR-6 from the PHP-Cache project like tagging, namespaces or hierarchy.
- This implementation does support something similar to [namespaces from the PHP-Cache project](http://php-cache.readthedocs.io/en/latest/namespace/), by supporting what in MODX are called _Cache Partitions_ or _Cache Providers_. When creating the Pool instance, you provide the partition name as the second parameter. Different partitions may have their own defaults for expiration time, caching driver, and the format in which they are stored. These are typically configured in the MODX System Settings, but may also be passed in the third `$options` property of the constructor. 
- The modCacheManager supports cache key with directories (e.g. `users/10/messages`), however `/` is a reserved character with PSR-6, so you can **not** use those with this caching implementation. Instead you can use different partitions. 

## Tests

This implementation follows the PSR-6 spec as tested with the [PSR-6 integration tests provided by the PHP-Cache project](http://php-cache.readthedocs.io/en/latest/implementing-cache-pools/integration-tests/). To run these tests:

1. Install MODX
2. Create a `config.core.php` file in the root of this project, pointing to the MODX core folder. There's an example in `config.core.sample.php`
3. Make sure dependencies are installed with `composer install`
4. Run `phpunit` from the project root. 