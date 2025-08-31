<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use HeyFrame\Core\Checkout\Cart\Extension\CheckoutCartRuleLoaderExtension;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Content\Rule\RuleEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Profiling\Profiler;
use HeyFrame\Core\System\Channel\ChannelContext;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\ResetInterface;

#[Package('checkout')]
class CartRuleLoader implements ResetInterface
{
    private const MAX_ITERATION = 7;

    private ?RuleCollection $rules = null;

    /**
     * @var array<string, float>
     */
    private array $currencyFactor = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractCartPersister $cartPersister,
        private readonly Processor $processor,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
        private readonly AbstractRuleLoader $ruleLoader,
        private readonly Connection $connection,
        private readonly CartFactory $cartFactory,
        private readonly ExtensionDispatcher $extensions,
    ) {
    }

    public function loadByToken(ChannelContext $context, string $cartToken): RuleLoaderResult
    {
        try {
            $cart = $this->cartPersister->load($cartToken, $context);

            return $this->load($context, $cart, new CartBehavior($context->getPermissions()), false);
        } catch (CartTokenNotFoundException) {
            $cart = $this->cartFactory->createNew($cartToken);

            return $this->load($context, $cart, new CartBehavior($context->getPermissions()), true);
        }
    }

    public function loadByCart(ChannelContext $context, Cart $cart, CartBehavior $behaviorContext, bool $isNew = false): RuleLoaderResult
    {
        return $this->load($context, $cart, $behaviorContext, $isNew);
    }

    public function reset(): void
    {
        $this->rules = null;
    }

    public function invalidate(): void
    {
        $this->reset();
        $this->cache->delete(CachedRuleLoader::CACHE_KEY);
    }

    private function load(ChannelContext $context, Cart $cart, CartBehavior $behaviorContext, bool $new): RuleLoaderResult
    {
        return Profiler::trace('cart-rule-loader', function () use ($context, $cart, $behaviorContext, $new) {
            // If the processing starts with deferred errors already in the cart, the cart MUST be persisted
            // to remove the errors from the stored cart
            $hasDeferredErrors = $cart->getErrors()->count() > 0;

            if (!Feature::isActive('DEFERRED_CART_ERRORS')) {
                $hasDeferredErrors = false;
            }

            $timestamps = $cart->getLineItems()->fmap(static fn (LineItem $lineItem) => $lineItem->getDataTimestamp()?->format(Defaults::STORAGE_DATE_TIME_FORMAT));
            $dataHashes = $cart->getLineItems()->fmap(static fn (LineItem $lineItem) => $lineItem->getDataContextHash());

            $result = $this->extensions->publish(
                name: CheckoutCartRuleLoaderExtension::NAME,
                extension: new CheckoutCartRuleLoaderExtension($context, $cart, $behaviorContext, $new),
                function: $this->_load(...),
            );

            // save the cart if errors exist, so the errors get persisted
            if ($this->updated($result->getCart(), $timestamps, $dataHashes)
                || $result->getCart()->getErrorHash() !== $result->getCart()->getErrors()->getUniqueHash()
                || $hasDeferredErrors
            ) {
                $result->getCart()->setErrorHash($result->getCart()->getErrors()->getUniqueHash());
                $this->cartPersister->save($result->getCart(), $context);
            }

            return $result;
        });
    }

    private function _load(ChannelContext $channelContext, Cart $originalCart, CartBehavior $cartBehavior, bool $new): RuleLoaderResult
    {
        $rules = $this->loadRules($channelContext->getContext());

        // save all rules for later usage
        $all = $rules;

        // For existing carts filter rules to only contain the rules from the current cart
        if ($new === false) {
            $rules = $rules->filter(
                fn (RuleEntity $rule) => \in_array($rule->getId(), $originalCart->getRuleIds(), true)
            );
        }

        // update rules in current context
        $channelContext->setRuleIds($rules->getIds());
        $channelContext->setAreaRuleIds($rules->getIdsByArea());

        // start first cart calculation to have all objects enriched
        $cart = $this->processor->process($originalCart, $channelContext, $cartBehavior);

        $iteration = 1;
        do {
            $compare = $cart;

            if ($iteration > self::MAX_ITERATION) {
                break;
            }

            // filter rules which matches to current scope
            $rules = $rules->filterMatchingRules($cart, $channelContext);

            // update matching rules in context
            $channelContext->setRuleIds($rules->getIds());
            $channelContext->setAreaRuleIds($rules->getIdsByArea());

            // calculate cart again
            $cart = $this->processor->process($cart, $channelContext, $cartBehavior);

            // check if the cart changed, in this case we have to recalculate the cart again
            $recalculate = $this->cartChanged($cart, $compare);

            // check if rules changed for the last calculated cart, in this case we have to recalculate
            $ruleCompare = $all->filterMatchingRules($cart, $channelContext);

            if (!$rules->equals($ruleCompare)) {
                $recalculate = true;
                $rules = $ruleCompare;
            }

            ++$iteration;
        } while ($recalculate);

        $index = 0;
        foreach ($rules as $rule) {
            ++$index;
            $this->logger->info(
                \sprintf('#%d Rule detection: %s with priority %d (id: %s)', $index, $rule->getName(), $rule->getPriority(), $rule->getId())
            );
        }

        $channelContext->setRuleIds($rules->getIds());
        $channelContext->setAreaRuleIds($rules->getIdsByArea());

        return new RuleLoaderResult($cart, $rules);
    }

    private function loadRules(Context $context): RuleCollection
    {
        if ($this->rules !== null) {
            return $this->rules;
        }

        return $this->rules = $this->ruleLoader->load($context)->filterForContext();
    }

    private function cartChanged(Cart $previous, Cart $current): bool
    {
        $previousLineItems = $previous->getLineItems();
        $currentLineItems = $current->getLineItems();

        return $previousLineItems->count() !== $currentLineItems->count()
            || $previous->getPrice()->getTotalPrice() !== $current->getPrice()->getTotalPrice()
            || $previousLineItems->getKeys() !== $currentLineItems->getKeys()
            || $previousLineItems->getTypes() !== $currentLineItems->getTypes();
    }

    /**
     * @param array<string, string> $timestamps
     * @param array<string, string> $dataHashes
     */
    private function updated(Cart $cart, array $timestamps, array $dataHashes): bool
    {
        foreach ($cart->getLineItems() as $lineItem) {
            $lineItemId = $lineItem->getId();
            if (!isset($timestamps[$lineItemId], $dataHashes[$lineItemId])) {
                return true;
            }

            $original = $timestamps[$lineItemId];

            $timestamp = $lineItem->getDataTimestamp() !== null ? $lineItem->getDataTimestamp()->format(Defaults::STORAGE_DATE_TIME_FORMAT) : null;

            if ($original !== $timestamp) {
                return true;
            }

            if ($dataHashes[$lineItemId] !== $lineItem->getDataContextHash()) {
                return true;
            }
        }

        return \count($timestamps) !== $cart->getLineItems()->count();
    }

    private function fetchCurrencyFactor(string $currencyId, ChannelContext $context): float
    {
        if ($currencyId === Defaults::CURRENCY) {
            return 1;
        }

        $currency = $context->getCurrency();
        if ($currencyId === $currency->getId()) {
            return $currency->getFactor();
        }

        if (\array_key_exists($currencyId, $this->currencyFactor)) {
            return $this->currencyFactor[$currencyId];
        }

        $currencyFactor = $this->connection->fetchOne(
            'SELECT `factor` FROM `currency` WHERE `id` = :currencyId',
            ['currencyId' => Uuid::fromHexToBytes($currencyId)]
        );

        if (!$currencyFactor) {
            throw CartException::currencyCannotBeFound();
        }

        return $this->currencyFactor[$currencyId] = (float) $currencyFactor;
    }
}
