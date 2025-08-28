<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Api;

use Doctrine\DBAL\Exception;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Event\CustomerGroupRegistrationAccepted;
use HeyFrame\Core\Checkout\Customer\Event\CustomerGroupRegistrationDeclined;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\Context\ChannelContextRestorer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('checkout')]
class CustomerGroupRegistrationActionController
{
    /**
     * @param EntityRepository<CustomerCollection> $customerRepository
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     *
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $customerGroupRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ChannelContextRestorer $restorer
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/_action/customer-group-registration/accept', name: 'api.customer-group.accept', methods: ['POST'], requirements: ['version' => '\d+'])]
    public function accept(Request $request, Context $context): JsonResponse
    {
        $customerIds = $this->getRequestCustomerIds($request);

        $silentError = $request->request->getBoolean('silentError');

        $customers = $this->fetchCustomers($customerIds, $context, $silentError);

        $updateData = [];

        foreach ($customers as $customer) {
            $updateData[] = [
                'id' => $customer->getId(),
                'requestedGroupId' => null,
                'groupId' => $customer->getRequestedGroupId(),
            ];
        }

        $this->customerRepository->update($updateData, $context);

        foreach ($customers as $customer) {
            $channelContext = $this->restorer->restoreByCustomer($customer->getId(), $context);

            /** @var CustomerEntity $customer */
            $customer = $channelContext->getCustomer();
            $customerGroupId = $customer->getGroupId();

            $criteria = (new Criteria([$customerGroupId]))
                ->setLimit(1);

            $customerRequestedGroup = $this->customerGroupRepository->search($criteria, $channelContext->getContext())->getEntities()->first();
            if (!$customerRequestedGroup) {
                throw CustomerException::customerGroupNotFound($customerGroupId);
            }

            $this->eventDispatcher->dispatch(new CustomerGroupRegistrationAccepted(
                $customer,
                $customerRequestedGroup,
                $channelContext->getContext()
            ));
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/_action/customer-group-registration/decline', name: 'api.customer-group.decline', methods: ['POST'], requirements: ['version' => '\d+'])]
    public function decline(Request $request, Context $context): JsonResponse
    {
        $customerIds = $this->getRequestCustomerIds($request);

        $silentError = $request->request->getBoolean('silentError');

        $customers = $this->fetchCustomers($customerIds, $context, $silentError);

        $updateData = [];

        foreach ($customers as $customer) {
            $updateData[] = [
                'id' => $customer->getId(),
                'requestedGroupId' => null,
            ];
        }

        $this->customerRepository->update($updateData, $context);

        foreach ($customers as $customer) {
            $customerId = $customer->getId();
            $channelContext = $this->restorer->restoreByCustomer($customerId, $context);

            $customer = $channelContext->getCustomer();
            if (!$customer) {
                throw CustomerException::customersNotFound([$customerId]);
            }
            $customerGroupId = $customer->getGroupId();

            $criteria = (new Criteria([$customerGroupId]))
                ->setLimit(1);

            $customerRequestedGroup = $this->customerGroupRepository->search($criteria, $channelContext->getContext())->getEntities()->first();
            if (!$customerRequestedGroup) {
                throw CustomerException::customerGroupNotFound($customerGroupId);
            }

            $this->eventDispatcher->dispatch(new CustomerGroupRegistrationDeclined(
                $customer,
                $customerRequestedGroup,
                $channelContext->getContext()
            ));
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @return array<string>
     */
    private function getRequestCustomerIds(Request $request): array
    {
        $customerIds = $request->request->all('customerIds');

        if (!empty($customerIds)) {
            $customerIds = array_unique($customerIds);
        }

        if (empty($customerIds)) {
            throw CustomerException::customerIdsParameterIsMissing();
        }

        return $customerIds;
    }

    /**
     * @param array<string> $customerIds
     *
     * @return array<CustomerEntity>
     */
    private function fetchCustomers(array $customerIds, Context $context, bool $silentError = false): array
    {
        $criteria = new Criteria($customerIds);
        $result = $this->customerRepository->search($criteria, $context);
        if ($result->getTotal() === 0) {
            throw CustomerException::customersNotFound($customerIds);
        }

        $customers = [];

        foreach ($result->getEntities() as $customer) {
            if (!$customer->getRequestedGroupId()) {
                if ($silentError === false) {
                    throw CustomerException::groupRequestNotFound($customer->getId());
                }

                continue;
            }

            $customers[] = $customer;
        }

        return $customers;
    }
}
