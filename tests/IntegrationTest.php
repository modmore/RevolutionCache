<?php
use Cache\IntegrationTests\CachePoolTest;

class PoolIntegrationTest extends CachePoolTest
{
    protected $skippedTests = [
        'testGetItemInvalidKeys' => 'Key validation has been added yet',
        'testGetItemsInvalidKeys' => 'Key validation has been added yet',
        'testHasItemInvalidKeys' => 'Key validation has been added yet',
        'testDeleteItemInvalidKeys' => 'Key validation has been added yet',
        'testDeleteItemsInvalidKeys' => 'Key validation has been added yet',
    ];

    protected $instance = 0;
    public function createCachePool()
    {
        global $modx;
        $this->instance++;
        return new \modmore\RevolutionCache\Pool($modx, 'revolutioncache_integration_tests');
    }
}