<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Controller;

use HeyFrame\Core\ChannelRequest;
use HeyFrame\Core\Checkout\Cart\ApiOrderCartService;
use HeyFrame\Core\Checkout\Cart\Channel\AbstractCartOrderRoute;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Cart\Processor;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Framework\Api\ApiException;
use HeyFrame\Core\Framework\Api\Exception\InvalidChannelIdException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\Constraint\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Channel\Context\ChannelContextPersister;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceInterface;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceParameters;
use HeyFrame\Core\System\Channel\Event\ChannelContextSwitchEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('framework')]
class ChannelProxyController extends AbstractController
{
    private const CUSTOMER_ID = ChannelContextService::CUSTOMER_ID;

    private const CHANNEL_ID = 'channelId';
    private const SEARCH_ROUTE = 'search';

    private const ADMIN_ORDER_PERMISSIONS = [
        CheckoutPermissions::ALLOW_PRODUCT_PRICE_OVERWRITES => true,
    ];

    protected Processor $processor;

    /**
     * @internal
     *
     * @param EntityRepository<ChannelCollection> $channelRepository
     */
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EntityRepository $channelRepository,
        protected DataValidator $validator,
        protected ChannelContextPersister $contextPersister,
        private readonly ChannelContextServiceInterface $contextService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ApiOrderCartService $adminOrderCartService,
        private readonly AbstractCartOrderRoute $orderRoute,
        private readonly CartService $cartService,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[Route(path: '/api/_proxy/front-api/{channelId}/{_path}', name: 'api.proxy.front-api', requirements: ['_path' => '.*'])]
    public function proxy(string $_path, string $channelId, Request $request, Context $context): Response
    {
        $channel = $this->fetchChannel($channelId, $context);

        $channelApiRequest = $this->setUpChannelApiRequest($_path, $channelId, $request, $channel, $context);

        return $this->wrapInChannelApiRoute($channelApiRequest, fn (): Response => $this->kernel->handle($channelApiRequest, HttpKernelInterface::SUB_REQUEST));
    }

    #[Route(path: '/api/_proxy-order/{channelId}', name: 'api.proxy-order.create')]
    public function proxyCreateOrder(string $channelId, Request $request, Context $context, RequestDataBag $data): Response
    {
        $this->fetchChannel($channelId, $context);

        $channelContext = $this->fetchChannelContext($channelId, $request, $context);

        $cart = $this->cartService->getCart($channelContext->getToken(), $channelContext);

        $order = $this->orderRoute->order($cart, $channelContext, $data)->getOrder();

        return new JsonResponse($order);
    }

    #[Route(
        path: '/api/_proxy/switch-customer',
        name: 'api.proxy.switch-customer',
        defaults: ['_acl' => ['api_proxy_switch-customer']],
        methods: ['PATCH']
    )]
    public function assignCustomer(Request $request, Context $context): Response
    {
        if (!$request->request->has(self::CHANNEL_ID)) {
            throw ApiException::channelIdParameterIsMissing();
        }

        $channelId = (string) $request->request->get('channelId');

        if (!$request->request->has(self::CUSTOMER_ID)) {
            throw ApiException::channelIdParameterIsMissing();
        }

        $this->fetchChannel($channelId, $context);

        $channelContext = $this->fetchChannelContext($channelId, $request, $context);

        $this->persistPermissions($request, $channelContext);

        $this->updateCustomerToContext($request->get(self::CUSTOMER_ID), $channelContext);

        $content = json_encode([
            PlatformRequest::HEADER_CONTEXT_TOKEN => $channelContext->getToken(),
        ], \JSON_THROW_ON_ERROR);
        $response = new Response();
        $response->headers->set('content-type', 'application/json');
        $response->setContent($content ?: null);

        return $response;
    }

    #[Route(path: '/api/_proxy/disable-automatic-promotions', name: 'api.proxy.disable-automatic-promotions', methods: ['PATCH'])]
    public function disableAutomaticPromotions(Request $request): JsonResponse
    {
        if (!$request->request->has(self::CHANNEL_ID)) {
            throw ApiException::channelIdParameterIsMissing();
        }

        $contextToken = $this->getContextToken($request);

        $channelId = (string) $request->request->get('channelId');

        $this->adminOrderCartService->addPermission($contextToken, CheckoutPermissions::SKIP_AUTOMATIC_PROMOTIONS, $channelId);

        return new JsonResponse();
    }

    #[Route(path: '/api/_proxy/enable-automatic-promotions', name: 'api.proxy.enable-automatic-promotions', methods: ['PATCH'])]
    public function enableAutomaticPromotions(Request $request): JsonResponse
    {
        if (!$request->request->has(self::CHANNEL_ID)) {
            throw ApiException::channelIdParameterIsMissing();
        }

        $contextToken = $this->getContextToken($request);

        $channelId = (string) $request->request->get('channelId');

        $this->adminOrderCartService->deletePermission($contextToken, CheckoutPermissions::SKIP_AUTOMATIC_PROMOTIONS, $channelId);

        return new JsonResponse();
    }

    /**
     * @param callable(): Response $call
     */
    private function wrapInChannelApiRoute(Request $request, callable $call): Response
    {
        $requestStackBackup = $this->clearRequestStackWithBackup($this->requestStack);
        $this->requestStack->push($request);

        try {
            return $call();
        } finally {
            $this->restoreRequestStack($this->requestStack, $requestStackBackup);
        }
    }

    private function setUpChannelApiRequest(string $path, string $channelId, Request $request, ChannelEntity $channel, Context $context): Request
    {
        $contextToken = $this->getContextToken($request);

        $server = array_merge($request->server->all(), ['REQUEST_URI' => '/front-api/' . $path]);
        $subrequest = $request->duplicate(null, null, [], null, null, $server);

        $subrequest->headers->set(PlatformRequest::HEADER_ACCESS_KEY, $channel->getAccessKey());
        $subrequest->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, $contextToken);
        $subrequest->attributes->set(PlatformRequest::ATTRIBUTE_OAUTH_CLIENT_ID, $channel->getAccessKey());

        $channelContext = $this->fetchChannelContext($channelId, $subrequest, $context);
        if ($path === self::SEARCH_ROUTE) {
            $channelContext->getContext()->addState(Context::ELASTICSEARCH_EXPLAIN_MODE);
        }
        $subrequest->attributes->set(PlatformRequest::ATTRIBUTE_CHANNEL_CONTEXT_OBJECT, $channelContext);
        $subrequest->attributes->set(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT, $channelContext->getContext());

        return $subrequest;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidChannelIdException
     */
    private function fetchChannel(string $channelId, Context $context): ChannelEntity
    {
        $channel = $this->channelRepository->search(new Criteria([$channelId]), $context)->getEntities()->get($channelId);

        if ($channel === null) {
            throw ApiException::invalidChannelId($channelId);
        }

        return $channel;
    }

    /**
     * @throws ConstraintViolationException
     */
    private function validateImitateCustomerDataFields(DataBag $data, Context $context): void
    {
        $definition = new DataValidationDefinition('impersonation.generate-token');

        $definition
            ->add(self::CHANNEL_ID, new Uuid(), new EntityExists(entity: 'channel', context: $context))
            ->add(self::CUSTOMER_ID, new Uuid(), new EntityExists(entity: 'customer', context: $context));

        $validationEvent = new BuildValidationEvent($definition, $data, $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        $this->validator->validate($data->all(), $definition);
    }

    private function getContextToken(Request $request): string
    {
        $contextToken = $request->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN);

        if ($contextToken === null) {
            $contextToken = Random::getAlphanumericString(32);
        }

        return $contextToken;
    }

    /**
     * @return array<Request>
     */
    private function clearRequestStackWithBackup(RequestStack $requestStack): array
    {
        $requestStackBackup = [];

        while ($requestStack->getMainRequest()) {
            $request = $requestStack->pop();

            if ($request === null) {
                continue;
            }

            $requestStackBackup[] = $request;
        }

        return $requestStackBackup;
    }

    /**
     * @param array<Request> $requestStackBackup
     */
    private function restoreRequestStack(RequestStack $requestStack, array $requestStackBackup): void
    {
        $this->clearRequestStackWithBackup($requestStack);

        foreach ($requestStackBackup as $backedUpRequest) {
            $requestStack->push($backedUpRequest);
        }
    }

    private function fetchChannelContext(string $channelId, Request $request, Context $originalContext): ChannelContext
    {
        $contextToken = $this->getContextToken($request);

        return $this->contextService->get(
            new ChannelContextServiceParameters(
                $channelId,
                $contextToken,
                $request->headers->get(PlatformRequest::HEADER_LANGUAGE_ID),
                $request->attributes->get(ChannelRequest::ATTRIBUTE_DOMAIN_CURRENCY_ID),
                null,
                $originalContext
            )
        );
    }

    private function updateCustomerToContext(string $customerId, ChannelContext $context): void
    {
        $data = new DataBag();
        $data->set(self::CUSTOMER_ID, $customerId);

        $definition = new DataValidationDefinition('context_switch');
        $parameters = $data->only(
            self::CUSTOMER_ID
        );

        $customerCriteria = new Criteria();
        $customerCriteria->addFilter(new EqualsFilter('customer.id', $parameters[self::CUSTOMER_ID]));

        $definition
            ->add(self::CUSTOMER_ID, new EntityExists(entity: 'customer', context: $context->getContext(), criteria: $customerCriteria))
        ;

        $this->validator->validate($parameters, $definition);

        $isSwitchNewCustomer = true;
        if ($context->getCustomer()) {
            // Check if customer switch to another customer or not
            $isSwitchNewCustomer = $context->getCustomerId() !== $parameters[self::CUSTOMER_ID];
        }

        if (!$isSwitchNewCustomer) {
            return;
        }

        $this->contextPersister->save(
            $context->getToken(),
            [
                'customerId' => $parameters[self::CUSTOMER_ID],
                'paymentMethodId' => null,
                'languageId' => null,
                'currencyId' => null,
            ],
            $context->getChannelId()
        );
        $event = new ChannelContextSwitchEvent($context, $data);
        $this->eventDispatcher->dispatch($event);
    }

    private function persistPermissions(Request $request, ChannelContext $channelContext): void
    {
        $contextToken = $channelContext->getToken();

        $channelId = $channelContext->getChannelId();

        $payload = $this->contextPersister->load($contextToken, $channelId);
        $requestPermissions = $request->get(ChannelContextService::PERMISSIONS);

        if (\in_array(ChannelContextService::PERMISSIONS, $payload, true) && !$requestPermissions) {
            return;
        }

        $payload[ChannelContextService::PERMISSIONS] = $requestPermissions
            ? \array_fill_keys($requestPermissions, true)
            : self::ADMIN_ORDER_PERMISSIONS;

        $this->contextPersister->save($contextToken, $payload, $channelId);
    }
}
