<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\Profiling\Profiler;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Event\ChannelContextCreatedEvent;
use HeyFrame\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntitySearcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

#[Package('framework')]
class ChannelContextService implements ChannelContextServiceInterface
{
    final public const CURRENCY_ID = 'currencyId';

    final public const LANGUAGE_ID = 'languageId';

    final public const CUSTOMER_ID = 'customerId';

    final public const CUSTOMER_GROUP_ID = 'customerGroupId';

    final public const PAYMENT_METHOD_ID = 'paymentMethodId';

    final public const COUNTRY_ID = 'countryId';

    final public const VERSION_ID = 'version-id';

    final public const PERMISSIONS = 'permissions';

    final public const DOMAIN_ID = 'domainId';

    final public const ORIGINAL_CONTEXT = 'originalContext';

    final public const IMITATING_USER_ID = 'imitatingUserId';

    /**
     * @internal do not rely on this externally, use the rules from the context instead
     */
    final public const RULE_IDS = 'sw-rule-ids';

    /**
     * @internal do not rely on this externally, use the rules from the context instead
     */
    final public const AREA_RULE_IDS = 'sw-rule-area-ids';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractChannelContextFactory $factory,
        private readonly CartRuleLoader $ruleLoader,
        private readonly ChannelContextPersister $contextPersister,
        private readonly CartService $cartService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack
    ) {
    }

    public function get(ChannelContextServiceParameters $parameters): ChannelContext
    {
        return Profiler::trace('channel-context', function () use ($parameters) {
            $token = $parameters->getToken();

            $session = $this->contextPersister->load($token, $parameters->getChannelId());

            if ($session['expired'] ?? false) {
                $token = Random::getAlphanumericString(32);
            }

            if ($parameters->getLanguageId() !== null) {
                $session[self::LANGUAGE_ID] = $parameters->getLanguageId();
            }

            if ($parameters->getCurrencyId() !== null && !\array_key_exists(self::CURRENCY_ID, $session)) {
                $session[self::CURRENCY_ID] = $parameters->getCurrencyId();
            }

            if ($parameters->getDomainId() !== null) {
                $session[self::DOMAIN_ID] = $parameters->getDomainId();
            }

            if ($parameters->getOriginalContext() !== null) {
                $session[self::ORIGINAL_CONTEXT] = $parameters->getOriginalContext();
            }

            if ($parameters->getCustomerId() !== null) {
                $session[self::CUSTOMER_ID] = $parameters->getCustomerId();
            }

            if ($parameters->getImitatingUserId() !== null) {
                $session[self::IMITATING_USER_ID] = $parameters->getImitatingUserId();
            }

            $context = $this->factory->create($token, $parameters->getChannelId(), $session);

            if ($parameters->getOriginalContext()?->hasState(ElasticsearchEntitySearcher::EXPLAIN_MODE)) {
                $context->addState(ElasticsearchEntitySearcher::EXPLAIN_MODE);
            }

            $this->eventDispatcher->dispatch(new ChannelContextCreatedEvent($context, $token, $session));

            $currentRequest = $this->requestStack->getCurrentRequest();
            $requestSession = $currentRequest?->hasSession() ? $currentRequest->getSession() : null;

            // skip cart calculation on ESI sub-requests if it has already been done.
            $esiRequest = $currentRequest?->attributes->has('_sw_esi') ?? false;
            if (!$this->cartService->hasCart($token) || !$esiRequest) {
                // @deprecated tag:v6.8.0 - Permission will always be true
                $result = $context->withPermissions(
                    [AbstractCartPersister::PERSIST_CART_ERROR_PERMISSION => Feature::isActive('DEFERRED_CART_ERRORS')],
                    fn (ChannelContext $context) => $this->ruleLoader->loadByToken($context, $token),
                );

                $this->cartService->setCart($result->getCart());

                // the rule loader updates the rules in the context, save them to the session for later reuse
                $requestSession?->set(self::RULE_IDS, $context->getRuleIds());
                $requestSession?->set(self::AREA_RULE_IDS, $context->getAreaRuleIds());
            } else {
                $context->setRuleIds($requestSession?->get(self::RULE_IDS) ?? []);
                $context->setAreaRuleIds($requestSession?->get(self::AREA_RULE_IDS) ?? []);
            }

            return $context;
        });
    }
}
