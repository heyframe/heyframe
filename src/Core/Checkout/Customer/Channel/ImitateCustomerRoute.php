<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\ImitateCustomerTokenGenerator;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\Constraint\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\AbstractChannelContextFactory;
use HeyFrame\Core\System\Channel\ContextTokenResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID], '_contextTokenRequired' => false])]
#[Package('checkout')]
class ImitateCustomerRoute extends AbstractImitateCustomerRoute
{
    final public const TOKEN = 'token';
    final public const CUSTOMER_ID = 'customerId';
    final public const USER_ID = 'userId';

    /**
     * @internal
     */
    public function __construct(
        private readonly AccountService $accountService,
        private readonly ImitateCustomerTokenGenerator $imitateCustomerTokenGenerator,
        private readonly AbstractLogoutRoute $logoutRoute,
        private readonly AbstractChannelContextFactory $channelContextFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DataValidator $validator
    ) {
    }

    public function getDecorated(): AbstractImitateCustomerRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/login/imitate-customer', name: 'store-api.account.imitate-customer-login', methods: ['POST'])]
    public function imitateCustomerLogin(RequestDataBag $requestDataBag, ChannelContext $context): ContextTokenResponse
    {
        $this->validateRequestDataFields($requestDataBag, $context->getContext());

        $customerId = $requestDataBag->getString(self::CUSTOMER_ID);

        if ($context->getCustomerId() === $customerId) {
            return new ContextTokenResponse($context->getToken());
        }

        $token = $requestDataBag->getString(self::TOKEN);
        $userId = $requestDataBag->getString(self::USER_ID);

        $this->imitateCustomerTokenGenerator->validate($token, $context->getChannelId(), $customerId, $userId);

        $context->setImitatingUserId($userId);

        if ($context->getCustomer()) {
            $newTokenResponse = $this->logoutRoute->logout($context, new RequestDataBag());

            $context = $this->channelContextFactory->create($newTokenResponse->getToken(), $context->getChannelId());
            $context->setImitatingUserId($userId);
        }

        $newToken = $this->accountService->loginById($customerId, $context);

        return new ContextTokenResponse($newToken);
    }

    /**
     * @throws ConstraintViolationException
     */
    private function validateRequestDataFields(DataBag $data, Context $context): void
    {
        $definition = new DataValidationDefinition('impersonation.login');

        $definition
            ->add(self::TOKEN, new NotBlank())
            ->add(self::CUSTOMER_ID, new Uuid(), new EntityExists(entity: 'customer', context: $context))
            ->add(self::USER_ID, new Uuid(), new EntityExists(entity: 'user', context: $context));

        $validationEvent = new BuildValidationEvent($definition, $data, $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        $this->validator->validate($data->all(), $definition);
    }
}
