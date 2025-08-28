<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class WishlistMergedEvent extends Event implements HeyFrameChannelEvent
{
    /**
     * @param array<array{id: string, productId?: string, productVersionId?: string}> $products
     */
    public function __construct(
        protected array $products,
        protected ChannelContext $context
    ) {
    }

    /**
     * @return array<array{id: string, productId?: string, productVersionId?: string}>
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }
}
