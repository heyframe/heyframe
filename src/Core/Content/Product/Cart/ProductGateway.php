<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cart;

use HeyFrame\Core\Content\Product\Events\ProductGatewayCriteriaEvent;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('inventory')]
class ProductGateway implements ProductGatewayInterface
{
    /**
     * @internal
     *
     * @param ChannelRepository<ProductCollection> $repository
     */
    public function __construct(
        private readonly ChannelRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param list<string> $ids
     */
    public function get(array $ids, ChannelContext $context): ProductCollection
    {
        $criteria = new Criteria($ids);
        $criteria->setTitle('cart::products');
        $criteria->addAssociation('cover.media');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('properties.group');

        $this->eventDispatcher->dispatch(
            new ProductGatewayCriteriaEvent($ids, $criteria, $context)
        );

        return $this->repository->search($criteria, $context)->getEntities();
    }
}
