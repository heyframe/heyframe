<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Facade\Traits;

use HeyFrame\Core\Checkout\Cart\Facade\CartFacadeHelper;
use HeyFrame\Core\Checkout\Cart\Facade\ContainerFacade;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
trait ContainerFactoryTrait
{
    private LineItemCollection $items;

    private CartFacadeHelper $helper;

    private ChannelContext $context;

    /**
     * The `container()` methods creates an empty container line-item with the given id and label.
     *
     * @param string $id The id for the new container line-item.
     * @param string|null $label The optional label of the container line-item.
     *
     * @return ContainerFacade Returns the newly created, empty container line-item.
     *
     * @example add-container/add-container.twig 7 Create a new container line-item, add products to it and apply a relative discount on the container.
     *
     * @internal
     */
    public function container(string $id, ?string $label = null): ContainerFacade
    {
        $item = new LineItem($id, LineItem::CONTAINER_LINE_ITEM, $id);
        $item->setLabel($label);
        $item->setRemovable(true);
        $item->setStackable(false);

        return new ContainerFacade($item, $this->priceStubs, $this->helper, $this->context);
    }
}
