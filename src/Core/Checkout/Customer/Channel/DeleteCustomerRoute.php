<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
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
class DeleteCustomerRoute extends AbstractDeleteCustomerRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(private readonly EntityRepository $customerRepository)
    {
    }

    public function getDecorated(): AbstractDeleteCustomerRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/customer', name: 'store-api.account.customer.delete', methods: ['DELETE'], defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true])]
    public function delete(ChannelContext $context, CustomerEntity $customer): NoContentResponse
    {
        $this->customerRepository->delete([['id' => $customer->getId()]], $context->getContext());

        return new NoContentResponse();
    }
}
