<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItemFactoryHandler;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\PriceDefinitionFactory;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Content\Product\Cart\ProductCartProcessor;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayEntity;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class ProductLineItemFactory implements LineItemFactoryInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly PriceDefinitionFactory $priceDefinitionFactory)
    {
    }

    public function supports(string $type): bool
    {
        return $type === LineItem::PRODUCT_LINE_ITEM_TYPE;
    }

    /**
     * @param array<mixed> $data
     */
    public function create(array $data, ChannelContext $context): LineItem
    {
        $lineItem = new LineItem($data['id'], LineItem::PRODUCT_LINE_ITEM_TYPE, $data['referencedId'] ?? $data['id'], $data['quantity'] ?? 1);
        $lineItem->markModified();

        $lineItem->setRemovable(true);
        $lineItem->setStackable(true);

        $this->update($lineItem, $data, $context);

        return $lineItem;
    }

    /**
     * @param array<mixed> $data
     */
    public function update(LineItem $lineItem, array $data, ChannelContext $context): void
    {
        if (isset($data['referencedId'])) {
            $lineItem->setReferencedId($data['referencedId']);
        }

        if (isset($data['payload'])) {
            $lineItem->setPayload($data['payload'] ?? []);
        }

        if (isset($data['quantity'])) {
            $lineItem->setQuantity((int) $data['quantity']);
        }

        if (isset($data['priceDefinition']) && !$context->hasPermission(CheckoutPermissions::ALLOW_PRODUCT_PRICE_OVERWRITES)) {
            throw CartException::insufficientPermission();
        }

        if (isset($data['priceDefinition'])) {
            $lineItem->addExtension(ProductCartProcessor::CUSTOM_PRICE, new ArrayEntity());
            $lineItem->setPriceDefinition($this->priceDefinitionFactory->factory($context->getContext(), $data['priceDefinition'], $data['type']));
        }
    }
}
