<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use HeyFrame\Core\Content\Flow\Exception\CustomerDeletedException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('after-sales')]
class CustomerStorer extends FlowStorer
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
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
        if (!$event instanceof CustomerAware || isset($stored[CustomerAware::CUSTOMER_ID])) {
            return $stored;
        }

        try {
            $stored[CustomerAware::CUSTOMER_ID] = $event->getCustomerId();
        } catch (CustomerDeletedException) {
        }

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(CustomerAware::CUSTOMER_ID)) {
            return;
        }

        $storable->setData(CustomerAware::CUSTOMER_ID, $storable->getStore(CustomerAware::CUSTOMER_ID));

        $storable->lazy(
            CustomerAware::CUSTOMER,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?CustomerEntity
    {
        $id = $storableFlow->getStore(CustomerAware::CUSTOMER_ID);
        if ($id === null) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadCustomer($criteria, $storableFlow->getContext(), $id);
    }

    private function loadCustomer(Criteria $criteria, Context $context, string $id): ?CustomerEntity
    {
        $event = new BeforeLoadStorableFlowDataEvent(
            CustomerDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        $customer = $this->customerRepository->search($criteria, $context)->getEntities()->get($id);

        if ($customer) {
            return $customer;
        }

        return null;
    }
}
