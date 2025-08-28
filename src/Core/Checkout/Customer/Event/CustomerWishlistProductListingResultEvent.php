<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Event;

use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class CustomerWishlistProductListingResultEvent extends NestedEvent implements HeyFrameChannelEvent
{
    final public const EVENT_NAME = 'checkout.customer.wishlist_listing_product_result';

    /**
     * @param EntitySearchResult<ProductCollection> $result
     */
    public function __construct(
        protected Request $request,
        protected EntitySearchResult $result,
        private ChannelContext $context
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return EntitySearchResult<ProductCollection>
     */
    public function getResult(): EntitySearchResult
    {
        return $this->result;
    }

    /**
     * @param EntitySearchResult<ProductCollection> $result
     */
    public function setResult(EntitySearchResult $result): void
    {
        $this->result = $result;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function setChannelContext(ChannelContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }
}
