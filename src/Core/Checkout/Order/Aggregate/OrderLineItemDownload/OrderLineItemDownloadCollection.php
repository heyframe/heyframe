<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItemDownload;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderLineItemDownloadEntity>
 */
#[Package('checkout')]
class OrderLineItemDownloadCollection extends EntityCollection
{
    public function filterByOrderLineItemId(string $id): self
    {
        return $this->filter(fn (OrderLineItemDownloadEntity $orderLineItemDownloadEntity) => $orderLineItemDownloadEntity->getOrderLineItemId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'order_line_item_download_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderLineItemDownloadEntity::class;
    }
}
