<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\CustomerGroupAware;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('after-sales')]
class CustomerGroupStorer extends FlowStorer
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     */
    public function __construct(
        private readonly EntityRepository $customerGroupRepository,
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
        if (!$event instanceof CustomerGroupAware || isset($stored[CustomerGroupAware::CUSTOMER_GROUP_ID])) {
            return $stored;
        }

        $stored[CustomerGroupAware::CUSTOMER_GROUP_ID] = $event->getCustomerGroupId();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(CustomerGroupAware::CUSTOMER_GROUP_ID)) {
            return;
        }

        $storable->setData(CustomerGroupAware::CUSTOMER_GROUP_ID, $storable->getStore(CustomerGroupAware::CUSTOMER_GROUP_ID));

        $storable->lazy(
            CustomerGroupAware::CUSTOMER_GROUP,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?CustomerGroupEntity
    {
        $id = $storableFlow->getStore(CustomerGroupAware::CUSTOMER_GROUP_ID);
        if ($id === null) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadCustomerGroup($criteria, $storableFlow->getContext(), $id);
    }

    private function loadCustomerGroup(Criteria $criteria, Context $context, string $id): ?CustomerGroupEntity
    {
        $event = new BeforeLoadStorableFlowDataEvent(
            CustomerGroupDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        $customerGroup = $this->customerGroupRepository->search($criteria, $context)->getEntities()->get($id);

        if ($customerGroup) {
            return $customerGroup;
        }

        return null;
    }
}
