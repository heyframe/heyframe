<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Facade;

use HeyFrame\Core\Checkout\Cart\Facade\Traits\ItemsAddTrait;
use HeyFrame\Core\Checkout\Cart\Facade\Traits\ItemsCountTrait;
use HeyFrame\Core\Checkout\Cart\Facade\Traits\ItemsHasTrait;
use HeyFrame\Core\Checkout\Cart\Facade\Traits\ItemsIteratorTrait;
use HeyFrame\Core\Checkout\Cart\Facade\Traits\ItemsRemoveTrait;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * The ItemsFacade is a wrapper around a collection of line-items.
 *
 * @script-service cart_manipulation
 *
 * @implements \IteratorAggregate<array-key, ItemFacade|ContainerFacade>
 */
#[Package('checkout')]
class ItemsFacade implements \IteratorAggregate, \Countable
{
    use ItemsAddTrait;
    use ItemsCountTrait;
    use ItemsHasTrait;
    use ItemsIteratorTrait;
    use ItemsRemoveTrait;

    /**
     * @internal
     */
    public function __construct(
        private LineItemCollection $items,
        private ScriptPriceStubs $priceStubs,
        private CartFacadeHelper $helper,
        private ChannelContext $context
    ) {
    }

    private function getItems(): LineItemCollection
    {
        return $this->items;
    }
}
