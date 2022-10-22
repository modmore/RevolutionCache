<?php

namespace modmore\RevolutionCache;

use MODX\Revolution\modX;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use xPDO\Cache\xPDOCache;

class Pool implements CacheItemPoolInterface
{
    protected \modX|modX $modx;

    /** @var null|\xPDOCache|xPDOCache */
    protected $provider;

    public function __construct(\modX|modX $modx, $provider = '', array $options = array())
    {
        $this->modx = $modx;

        $cacheManager = $this->modx->getCacheManager();
        $this->provider = $cacheManager->getCacheProvider($provider, $options);
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem(string $key): CacheItemInterface
    {
        $this->_validateKey($key);

        $hit = false;
        $value = $this->provider->get($key);
        if ($value !== null) {
            $hit = true;
        }

        if (is_array($value) && array_key_exists('_type', $value) && !empty($value['_type'])) {
            $value = $this->modx->newObject($value['_type']);
            $value->fromArray($value['_fields']);
            $value->_new = $value['_new'];
        }
        else {
            $unserialized = @unserialize($value);
            if ($unserialized !== false || $value === 'b:0;') {
                $value = $unserialized;
            }
        }

        return new Item($this->modx, $key, $value, $hit);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param string[] $keys
     *   An indexed array of keys of items to retrieve.
     *
     * @return iterable A traversable collection of Cache Items keyed by the cache keys of
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array()): iterable
    {
        $items = [];
        foreach ($keys as $key) {
            $item = $this->getItem($key);
            $items[$item->getKey()] = $item;
        }
        return new \ArrayIterator($items);
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *   The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if item exists in the cache, false otherwise.
     */
    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear(): bool
    {
        return $this->provider->flush();
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key to delete.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem(string $key): bool
    {
        $this->_validateKey($key);
        $deleted = $this->provider->delete($key);
        if (!$deleted && $this->hasItem($key)) {
            return false;
        }
        return true;
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param string[] $keys
     *   An array of keys that should be removed from the pool.
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item): bool
    {
        $value = $item->get();

        if ($value instanceof \xPDOObject) {
            $value = [
                '_type' => $value->_class,
                '_fields' => $value->toArray('', true),
                '_new' => $value->_new,
            ];
        }
        elseif ($value === null || !is_scalar($value)) {
            $value = serialize($value);
        }
        return $this->provider->set($item->getKey(), $value, ($item instanceof Item) ? $item->_getExpiration() : null);
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit(): bool
    {
        return true;
    }

    /**
     * Validates a cache key to make sure it does not contain invalid characters.
     *
     * @param mixed $key
     */
    protected function _validateKey(string $key): void
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(sprintf('Cache key must be a string, "%s" given', is_object($key) ? get_class($key) : gettype($key)));
        }
        if ($key === '') {
            throw new InvalidArgumentException('Cache key cannot be empty');
        }

        if (isset($key[strcspn($key, '{}()\/@:')])) {
            throw new InvalidArgumentException(sprintf('Cache key "%s" contains reserved characters {}()/\@:', $key));
        }
    }
}