<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('discovery')]
class CategoryLevelLoaderCacheKeyEvent extends Event implements HeyFrameChannelEvent
{
    private bool $shouldCache = true;

    /**
     * @param array<string, mixed> $parts
     */
    public function __construct(
        private array $parts,
        public readonly string $rootId,
        public readonly int $depth,
        public readonly ChannelContext $context,
        public readonly Criteria $criteria
    ) {
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    /**
     * @return array<string, mixed>
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param array<string, mixed> $parts
     */
    public function setParts(array $parts): void
    {
        $this->parts = $parts;
    }

    public function addPart(string $key, string $part): void
    {
        $this->parts[$key] = $part;
    }

    public function removePart(string $part): void
    {
        unset($this->parts[$part]);
    }

    public function disableCaching(): void
    {
        $this->shouldCache = false;
    }

    public function shouldCache(): bool
    {
        return $this->shouldCache;
    }
}
