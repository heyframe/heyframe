<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict;

use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class DictService
{
    /**
     * @internal
     */
    public function __construct(
        private readonly DictLoader $dictLoader,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function get(string $key, Context $context): DictEntity
    {
        $this->cacheTagCollector->addTag('system.dict-' . $key);
        $dictCollection = $this->dictLoader->load($key, $context);
        return $dictCollection->filterKey($key);
    }
}
