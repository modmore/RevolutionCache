<?php
use Cache\IntegrationTests\CachePoolTest;

class PoolIntegrationTest extends CachePoolTest
{
    public function createCachePool()
    {
        global $modx;
        return new \modmore\RevolutionCache\Pool($modx, 'revolutioncache_integration_tests');
    }
}