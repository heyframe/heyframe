<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Framework\Adapter\Cache\CacheValueCompressor;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Hasher;
use HeyFrame\Core\System\Channel\BaseChannelContext;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @internal
 */
#[Package('framework')]
class CachedBaseChannelContextFactory extends AbstractBaseChannelContextFactory
{
    public function __construct(
        private readonly AbstractBaseChannelContextFactory $decorated,
        private readonly CacheInterface $cache,
    ) {
    }

    public function create(string $channelId, array $options = []): BaseChannelContext
    {
        if (isset($options[ChannelContextService::ORIGINAL_CONTEXT])) {
            return $this->decorated->create($channelId, $options);
        }
        if (isset($options[ChannelContextService::PERMISSIONS])) {
            return $this->decorated->create($channelId, $options);
        }

        $name = self::buildName($channelId);

        ksort($options);

        $keys = \array_intersect_key($options, [
            ChannelContextService::CURRENCY_ID => true,
            ChannelContextService::LANGUAGE_ID => true,
            ChannelContextService::DOMAIN_ID => true,
            ChannelContextService::PAYMENT_METHOD_ID => true,
            ChannelContextService::SHIPPING_METHOD_ID => true,
            ChannelContextService::VERSION_ID => true,
            ChannelContextService::COUNTRY_ID => true,
            ChannelContextService::COUNTRY_STATE_ID => true,
        ]);

        $key = implode('-', [$name, Hasher::hash($keys)]);

        $value = $this->cache->get($key, function (ItemInterface $item) use ($name, $channelId, $options) {
            $item->tag([$name, CachedChannelContextFactory::ALL_TAG]);

            return CacheValueCompressor::compress(
                $this->decorated->create($channelId, $options)
            );
        });

        return CacheValueCompressor::uncompress($value);
    }

    public static function buildName(string $channelId): string
    {
        return 'base-context-factory-' . $channelId;
    }
}
