<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use HeyFrame\Core\Content\Flow\Dispatching\Aware\OrderTransactionAware;
use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OrderTransactionStorer extends FlowStorer
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderTransactionCollection> $orderTransactionRepository
     */
    public function __construct(
        private readonly EntityRepository $orderTransactionRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @param array<string, mixed> $stored
     *
     * @return array<string, mixed>
     */
    public function store(FlowEventAware $event, array $stored): array
    {
        if (!$event instanceof OrderTransactionAware || isset($stored[OrderTransactionAware::ORDER_TRANSACTION_ID])) {
            return $stored;
        }

        $stored[OrderTransactionAware::ORDER_TRANSACTION_ID] = $event->getOrderTransactionId();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(OrderTransactionAware::ORDER_TRANSACTION_ID)) {
            return;
        }

        $storable->lazy(
            OrderTransactionAware::ORDER_TRANSACTION,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?OrderTransactionEntity
    {
        $id = $storableFlow->getStore(OrderTransactionAware::ORDER_TRANSACTION_ID);
        if ($id === null) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadOrderTransaction($criteria, $storableFlow->getContext(), $id);
    }

    private function loadOrderTransaction(Criteria $criteria, Context $context, string $id): ?OrderTransactionEntity
    {
        $event = new BeforeLoadStorableFlowDataEvent(
            OrderTransactionDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        $orderTransaction = $this->orderTransactionRepository->search($criteria, $context)->getEntities()->get($id);

        if ($orderTransaction) {
            return $orderTransaction;
        }

        return null;
    }
}
