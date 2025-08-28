<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class CustomerGroupRegistrationSettingsRoute extends AbstractCustomerGroupRegistrationSettingsRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     */
    public function __construct(private readonly EntityRepository $customerGroupRepository)
    {
    }

    public function getDecorated(): AbstractCustomerGroupRegistrationSettingsRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/customer-group-registration/config/{customerGroupId}', name: 'store-api.customer-group-registration.config', methods: ['GET'])]
    public function load(string $customerGroupId, ChannelContext $context): CustomerGroupRegistrationSettingsRouteResponse
    {
        $criteria = (new Criteria([$customerGroupId]))
            ->addFilter(new EqualsFilter('registrationActive', 1))
            ->addFilter(new EqualsFilter('registrationChannels.id', $context->getChannelId()));

        $result = $this->customerGroupRepository->search($criteria, $context->getContext());
        if ($result->getTotal() === 0) {
            throw CustomerException::customerGroupRegistrationConfigurationNotFound($customerGroupId);
        }

        $customerGroup = $result->first();
        \assert($customerGroup !== null);

        return new CustomerGroupRegistrationSettingsRouteResponse($customerGroup);
    }
}
