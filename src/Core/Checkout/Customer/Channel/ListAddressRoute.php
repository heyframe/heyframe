<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\Event\AddressListingCriteriaEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class ListAddressRoute extends AbstractListAddressRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerAddressCollection> $addressRepository
     */
    public function __construct(
        private readonly EntityRepository $addressRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getDecorated(): AbstractListAddressRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/list-address', name: 'store-api.account.address.list.get', methods: ['GET', 'POST'], defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_entity' => 'customer_address'])]
    public function load(Criteria $criteria, ChannelContext $context, CustomerEntity $customer): ListAddressRouteResponse
    {
        $criteria
            ->addAssociation('salutation')
            ->addAssociation('country')
            ->addAssociation('countryState')
            ->addFilter(new EqualsFilter('customer_address.customerId', $customer->getId()));

        $this->eventDispatcher->dispatch(new AddressListingCriteriaEvent($criteria, $context));

        return new ListAddressRouteResponse($this->addressRepository->search($criteria, $context->getContext()));
    }
}
