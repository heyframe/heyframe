<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\Event\CustomerSetDefaultBillingAddressEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerSetDefaultShippingAddressEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class SwitchDefaultAddressRoute extends AbstractSwitchDefaultAddressRoute
{
    use CustomerAddressValidationTrait;

    /**
     * @internal
     *
     * @param EntityRepository<CustomerAddressCollection> $addressRepository
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(
        private readonly EntityRepository $addressRepository,
        private readonly EntityRepository $customerRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getDecorated(): AbstractSwitchDefaultAddressRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/account/address/default-shipping/{addressId}',
        name: 'store-api.account.address.change.default.shipping',
        defaults: ['type' => 'shipping', '_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['PATCH']
    )]
    #[Route(
        path: '/store-api/account/address/default-billing/{addressId}',
        name: 'store-api.account.address.change.default.billing',
        defaults: ['type' => 'billing', '_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['PATCH']
    )]
    public function swap(string $addressId, string $type, ChannelContext $context, CustomerEntity $customer): NoContentResponse
    {
        $this->validateAddress($addressId, $context, $customer);

        switch ($type) {
            case self::TYPE_BILLING:
                $data = [
                    'id' => $customer->getId(),
                    'defaultBillingAddressId' => $addressId,
                ];

                $event = new CustomerSetDefaultBillingAddressEvent($context, $customer, $addressId);
                $this->eventDispatcher->dispatch($event);

                break;
            default:
                $data = [
                    'id' => $customer->getId(),
                    'defaultShippingAddressId' => $addressId,
                ];

                $event = new CustomerSetDefaultShippingAddressEvent($context, $customer, $addressId);
                $this->eventDispatcher->dispatch($event);

                break;
        }

        $this->customerRepository->update([$data], $context->getContext());

        return new NoContentResponse();
    }
}
