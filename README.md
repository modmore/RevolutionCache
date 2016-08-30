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

$pool = new \modmore\RevolutionCache\Pool($modx, 'my_package');

// Do some cache stuff
$item = $pool->getItem('my/value');
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

## Notes

- This caching implementation does not support deferred saving: `$pool->saveDeferred()` is identical to `$pool->save()` and `$pool->commit()` is always true.
- The implementation deviates from the PSR-6 standard by allowing the `/` character in the key name. 
- The Pool class does **not** support extensions to PSR-6 from the PHP-Cache project like tagging, namespaces or hierarchy.