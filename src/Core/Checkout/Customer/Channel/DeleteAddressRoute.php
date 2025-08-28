<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class DeleteAddressRoute extends AbstractDeleteAddressRoute
{
    use CustomerAddressValidationTrait;

    /**
     * @param EntityRepository<CustomerAddressCollection> $addressRepository
     *
     * @internal
     */
    public function __construct(private readonly EntityRepository $addressRepository)
    {
    }

    public function getDecorated(): AbstractDeleteAddressRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/account/address/{addressId}',
        name: 'store-api.account.address.delete',
        defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['DELETE']
    )]
    public function delete(string $addressId, ChannelContext $context, CustomerEntity $customer): NoContentResponse
    {
        $this->validateAddress($addressId, $context, $customer);

        if (
            $addressId === $customer->getDefaultBillingAddressId()
            || $addressId === $customer->getDefaultShippingAddressId()
        ) {
            throw CustomerException::cannotDeleteDefaultAddress($addressId);
        }

        $activeBillingAddress = $customer->getActiveBillingAddress();
        $activeShippingAddress = $customer->getActiveShippingAddress();

        if (
            ($activeBillingAddress && $addressId === $activeBillingAddress->getId())
            || ($activeShippingAddress && $addressId === $activeShippingAddress->getId())
        ) {
            throw CustomerException::cannotDeleteActiveAddress($addressId);
        }

        $this->addressRepository->delete([['id' => $addressId]], $context->getContext());

        return new NoContentResponse();
    }
}
