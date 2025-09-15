<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CustomerRoute extends AbstractCustomerRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(private readonly EntityRepository $customerRepository)
    {
    }

    public function getDecorated(): AbstractCustomerRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/account/customer', name: 'front-api.account.customer', defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_entity' => 'customer'], methods: ['GET', 'POST'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria, CustomerEntity $customer): CustomerResponse
    {
        $criteria->setIds([$customer->getId()]);

        $customerEntity = $this->customerRepository->search($criteria, $context->getContext())->first();
        \assert($customerEntity !== null);

        return new CustomerResponse($customerEntity);
    }
}
