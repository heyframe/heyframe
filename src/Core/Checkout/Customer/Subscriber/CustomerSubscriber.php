<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Framework\Api\Acl\Front\Role\CustomerRoleCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class CustomerSubscriber implements EventSubscriberInterface
{
    /**
     * @param EntityRepository<CustomerCollection> $customerRepository
     * @param EntityRepository<CustomerRoleCollection> $customerRoleRepository
     */
    public function __construct(
        protected readonly EntityRepository $customerRepository,
        protected readonly EntityRepository $customerRoleRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::CUSTOMER_WRITTEN_EVENT => 'onCustomerWritten',
        ];
    }

    public function onCustomerWritten(EntityWrittenEvent $event): void
    {
        $newCustomerIds = [];
        foreach ($event->getWriteResults() as $writeResult) {
            if ($writeResult->getOperation() !== EntityWriteResult::OPERATION_INSERT) {
                continue;
            }
            $payload = $writeResult->getPayload();
            $customerId = $payload['id'] ?? null;

            if ($customerId) {
                $newCustomerIds[] = $customerId;
            }
        }
        if (\count($newCustomerIds) === 0) {
            return;
        }
        $this->assignDefaultRoleToCustomers($newCustomerIds, $event->getContext());
    }

    /**
     * @param string[] $customerIds
     */
    private function assignDefaultRoleToCustomers(array $customerIds, Context $context): void
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('name', 'default'));
        $defaultRole = $this->customerRoleRepository->search($criteria, $context)->getEntities()->first();
        if ($defaultRole === null) {
            return;
        }
        $mappings = [];

        foreach ($customerIds as $customerId) {
            $mappings[] = [
                'id' => $customerId,
                'roles' => [
                    [
                        'id' => $defaultRole->getId(),
                    ],
                ],
            ];
        }

        $this->customerRepository->upsert($mappings, $context);
    }
}
