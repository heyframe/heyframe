<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Stock;

use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Product\ProductEvents;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Entity\ChannelEntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class LoadProductStockSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly AbstractStockStorage $stockStorage)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'channel.' . ProductEvents::PRODUCT_LOADED_EVENT => ['channelLoaded', 50],
            'channel.product.partial_loaded' => ['channelLoaded', 50],
        ];
    }

    /**
     * @param ChannelEntityLoadedEvent<ProductEntity|PartialEntity> $event
     */
    public function channelLoaded(ChannelEntityLoadedEvent $event): void
    {
        $stocks = $this->stockStorage->load(
            new StockLoadRequest($event->getIds()),
            $event->getChannelContext()
        );

        foreach ($event->getEntities() as $product) {
            $stock = $stocks->getStockForProductId($product->getId());

            if ($stock === null) {
                continue;
            }

            $product->assign([
                // required stock data
                'stock' => $stock->stock,
                'available' => $stock->available,
                // optional stock data
                'minPurchase' => $stock->minPurchase ?? $product->get('minPurchase'),
                'maxPurchase' => $stock->maxPurchase ?? $product->get('maxPurchase'),
                'isCloseout' => $stock->isCloseout ?? $product->get('isCloseout'),
            ]);

            // allow for arbitrary stock data to be added to the product
            $product->addExtension('stock_data', $stock);
        }
    }
}
