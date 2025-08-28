<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\ProductAware;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProductStorer extends FlowStorer
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function store(FlowEventAware $event, array $stored): array
    {
        if (!$event instanceof ProductAware || isset($stored[ProductAware::PRODUCT_ID])) {
            return $stored;
        }

        $stored[ProductAware::PRODUCT_ID] = $event->getProductId();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(ProductAware::PRODUCT_ID)) {
            return;
        }

        $storable->lazy(
            ProductAware::PRODUCT,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?ProductEntity
    {
        $id = $storableFlow->getStore(ProductAware::PRODUCT_ID);
        if ($id === null) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadProduct($criteria, $storableFlow->getContext(), $id);
    }

    private function loadProduct(Criteria $criteria, Context $context, string $id): ?ProductEntity
    {
        $context->setConsiderInheritance(true);

        $event = new BeforeLoadStorableFlowDataEvent(
            ProductDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        return $this->productRepository->search($criteria, $context)->getEntities()->get($id);
    }
}
