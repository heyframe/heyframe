<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidationFactoryInterface;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID], '_contextTokenRequired' => true])]
#[Package('checkout')]
class ConvertGuestRoute extends AbstractConvertGuestRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DataValidator $validator,
        private readonly DataValidationFactoryInterface $passwordValidationFactory,
    ) {
    }

    public function getDecorated(): AbstractConvertGuestRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/convert-guest', name: 'store-api.account.convert-guest', methods: ['POST'], defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true])]
    public function convertGuest(RequestDataBag $requestDataBag, ChannelContext $context, CustomerEntity $customer, ?DataValidationDefinition $additionalValidationDefinitions = null): SuccessResponse
    {
        if (!$customer->getGuest()) {
            throw CustomerException::registeredCustomerCannotBeConverted($customer->getId());
        }

        $customerData = [
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'guest' => false,
            'password' => $requestDataBag->get('password'),
        ];

        $this->validate(new DataBag($customerData), $context, $additionalValidationDefinitions);

        $this->customerRepository->update([$customerData], $context->getContext());

        return new SuccessResponse();
    }

    private function validate(DataBag $data, ChannelContext $context, ?DataValidationDefinition $additionalValidationDefinitions = null): void
    {
        $definition = new DataValidationDefinition('customer.guest.convert');
        $definition->merge($this->passwordValidationFactory->create($context));

        if ($additionalValidationDefinitions) {
            $definition->merge($additionalValidationDefinitions);
        }

        $definition->add('email', new CustomerEmailUnique(channelContext: $context));

        $validationEvent = new BuildValidationEvent($definition, $data, $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        $this->validator->validate($data->all(), $definition);
    }
}
