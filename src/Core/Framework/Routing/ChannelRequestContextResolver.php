<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\ChannelRequest;
use HeyFrame\Core\Framework\Api\Context\ChannelApiSource;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\Event\ChannelContextResolvedEvent;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceInterface;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceParameters;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class ChannelRequestContextResolver implements RequestContextResolverInterface
{
    use RouteScopeCheckTrait;

    /**
     * @internal
     */
    public function __construct(
        private readonly RequestContextResolverInterface $decorated,
        private readonly ChannelContextServiceInterface $contextService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RouteScopeRegistry $routeScopeRegistry,
        private readonly Connection $connection,
    ) {
    }

    public function resolve(Request $request): void
    {
        if (!$request->attributes->has(PlatformRequest::ATTRIBUTE_CHANNEL_ID)) {
            $this->decorated->resolve($request);

            return;
        }

        if (!$this->isRequestScoped($request, ChannelContextRouteScopeDependant::class)) {
            return;
        }

        if (!$request->headers->has(PlatformRequest::HEADER_CONTEXT_TOKEN)) {
            if ($this->contextTokenRequired($request)) {
                throw RoutingException::missingRequestParameter(PlatformRequest::HEADER_CONTEXT_TOKEN);
            }

            $request->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, Random::getAlphanumericString(32));
        }

        // Retrieve context for current request
        $usedContextToken = (string) $request->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN);

        // Check for currency header first, fallback to domain currency attribute
        $currencyId = $request->headers->get(PlatformRequest::HEADER_CURRENCY_ID);
        if ($currencyId === null) {
            $currencyId = $request->attributes->get(ChannelRequest::ATTRIBUTE_DOMAIN_CURRENCY_ID);
        }

        $contextServiceParameters = new ChannelContextServiceParameters(
            (string) $request->attributes->get(PlatformRequest::ATTRIBUTE_CHANNEL_ID),
            $usedContextToken,
            $request->headers->get(PlatformRequest::HEADER_LANGUAGE_ID),
            $currencyId,
            $request->attributes->get(ChannelRequest::ATTRIBUTE_DOMAIN_ID),
            $request->attributes->get(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT),
            null,
        );
        $context = $this->contextService->get($contextServiceParameters);

        // Validate if a customer login is required for the current request
        $this->validateLogin($request, $context);

        // Update attributes and headers of the current request
        $request->attributes->set(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT, $context->getContext());
        $request->attributes->set(PlatformRequest::ATTRIBUTE_CHANNEL_CONTEXT_OBJECT, $context);
        $request->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, $context->getToken());

        $this->eventDispatcher->dispatch(
            new ChannelContextResolvedEvent($context, $usedContextToken)
        );
    }

    protected function getScopeRegistry(): RouteScopeRegistry
    {
        return $this->routeScopeRegistry;
    }

    private function contextTokenRequired(Request $request): bool
    {
        return (bool) $request->attributes->get(PlatformRequest::ATTRIBUTE_CONTEXT_TOKEN_REQUIRED, false);
    }

    private function validateLogin(Request $request, ChannelContext $context): void
    {
        if (!$request->attributes->get(PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED)) {
            return;
        }

        if ($context->getCustomer() === null) {
            throw RoutingException::customerNotLoggedIn();
        }

        $chennelSource = $context->getContext()->getSource();
        if ($chennelSource instanceof ChannelApiSource) {
            $chennelSource->setPermissions($this->fetchPermissions($context->getCustomer()->getId()));
        }
    }

    /**
     * @return string[]
     */
    private function fetchPermissions(string $customerId): array
    {
        $permissions = $this->connection->createQueryBuilder()
            ->select('role.privileges')
            ->from('customer_role_mapping', 'mapping')
            ->innerJoin('mapping', 'customer_role', 'role', 'mapping.customer_role_id = role.id')
            ->where('mapping.customer_id = :customerId')
            ->setParameter('customerId', Uuid::fromHexToBytes($customerId))
            ->executeQuery()
            ->fetchFirstColumn();

        $list = [];
        foreach ($permissions as $privileges) {
            $privileges = json_decode((string) $privileges, true, 512, \JSON_THROW_ON_ERROR);
            $list = array_merge($list, $privileges);
        }

        return array_unique(array_filter($list));
    }
}
