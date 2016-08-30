<?php

namespace modmore\RevolutionCache;

use Psr\Cache\CacheException as CacheExceptionInterface;

class CacheException extends \Exception implements CacheExceptionInterface
{

}