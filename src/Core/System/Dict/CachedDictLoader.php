<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict;

use HeyFrame\Core\Framework\Adapter\Cache\CacheValueCompressor;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Package('framework')]
class CachedDictLoader extends AbstractDictLoader
{
    final public const CACHE_TAG = 'system-dict';

    public function __construct(
        private readonly AbstractDictLoader $decorated,
        private readonly CacheInterface $cache
    ) {
    }

    public function getDecorated(): AbstractDictLoader
    {
        return $this->decorated;
    }

    public function load(?string $key, Context $context): DictCollection
    {
        $key = 'system-dict-' . $key;

        return $this->cache->get($key, function (ItemInterface $item) use ($key, $context) {
            $dict = $this->getDecorated()->load($key, $context);

            $item->tag([self::CACHE_TAG]);

            return CacheValueCompressor::compress($dict);
        });
    }
}
