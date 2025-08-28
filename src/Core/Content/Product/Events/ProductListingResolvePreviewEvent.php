<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Events;

use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
class ProductListingResolvePreviewEvent extends NestedEvent implements HeyFrameChannelEvent
{
    /**
     * @param array<string> $mapping
     */
    public function __construct(
        protected ChannelContext $context,
        protected Criteria $criteria,
        protected array $mapping,
        protected bool $hasOptionFilter
    ) {
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    /**
     * @return array<string>
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function replace(string $originalId, string $newId): void
    {
        if (!\array_key_exists($originalId, $this->mapping)) {
            throw ProductException::originalIdNotFound($originalId);
        }

        $this->mapping[$originalId] = $newId;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function hasOptionFilter(): bool
    {
        return $this->hasOptionFilter;
    }
}
