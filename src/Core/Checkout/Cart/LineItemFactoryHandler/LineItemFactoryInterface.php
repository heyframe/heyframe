<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItemFactoryHandler;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * A LineItemFactory is, as the name suggests, responsible for creating a LineItem for the shopping cart.
 * Even if the Cart\LineItem is kept abstract, some LineItems need additional data to be created.
 * Since this is knowledge from the LineItem's processor, it should not be necessary to know this knowledge as a user of the cart.
 */
#[Package('checkout')]
interface LineItemFactoryInterface
{
    public function supports(string $type): bool;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, ChannelContext $context): LineItem;

    /**
     * @param array<string, mixed> $data
     */
    public function update(LineItem $lineItem, array $data, ChannelContext $context): void;
}
