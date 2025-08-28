<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Facade\Traits;

use HeyFrame\Core\Checkout\Cart\Facade\CartFacadeHelper;
use HeyFrame\Core\Checkout\Cart\Facade\ContainerFacade;
use HeyFrame\Core\Checkout\Cart\Facade\ItemFacade;
use HeyFrame\Core\Checkout\Cart\Facade\ScriptPriceStubs;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
trait ItemsGetTrait
{
    private LineItemCollection $items;

    private CartFacadeHelper $helper;

    private ChannelContext $context;

    private ScriptPriceStubs $priceStubs;

    /**
     * `get()` returns the line-item with the given id from this collection.
     *
     * @param string $id The id of the line-item that should be returned.
     *
     * @return ItemFacade|null The line-item with the given id, or null if it does not exist.
     */
    public function get(string $id): ?ItemFacade
    {
        $item = $this->getItems()->get($id);

        if (!$item instanceof LineItem) {
            return null;
        }

        return match ($item->getType()) {
            LineItem::CONTAINER_LINE_ITEM => new ContainerFacade($item, $this->priceStubs, $this->helper, $this->context),
            default => new ItemFacade($item, $this->priceStubs, $this->helper, $this->context),
        };
    }
}
