<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Framework\Adapter\Cache\CacheValueCompressor;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Hasher;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Package('framework')]
class CachedChannelContextFactory extends AbstractChannelContextFactory
{
    final public const ALL_TAG = 'channel-context';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractChannelContextFactory $decorated,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getDecorated(): AbstractChannelContextFactory
    {
        return $this->decorated;
    }

    public function create(string $token, string $channelId, array $options = []): ChannelContext
    {
        $name = self::buildName($channelId);

        if (!$this->isCacheable($options)) {
            return $this->getDecorated()->create($token, $channelId, $options);
        }

        ksort($options);

        $key = implode('-', [$name, Hasher::hash($options)]);

        $value = $this->cache->get($key, function (ItemInterface $item) use ($name, $token, $channelId, $options) {
            $item->tag([$name, self::ALL_TAG]);

            return CacheValueCompressor::compress(
                $this->decorated->create($token, $channelId, $options)
            );
        });

        $context = CacheValueCompressor::uncompress($value);

        if (!$context instanceof ChannelContext) {
            return $this->getDecorated()->create($token, $channelId, $options);
        }

        $context->assign(['token' => $token]);

        return $context;
    }

    public static function buildName(string $channelId): string
    {
        return 'context-factory-' . $channelId;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function isCacheable(array $options): bool
    {
        return !isset($options[ChannelContextService::CUSTOMER_ID]);
    }
}
