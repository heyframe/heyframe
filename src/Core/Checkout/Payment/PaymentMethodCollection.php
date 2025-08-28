<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\RuleIdMatcher;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @extends EntityCollection<PaymentMethodEntity>
 */
#[Package('checkout')]
class PaymentMethodCollection extends EntityCollection
{
    /**
     * @deprecated tag:v6.8.0 use RuleIdMatcher instead
     */
    public function filterByActiveRules(ChannelContext $channelContext): PaymentMethodCollection
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', RuleIdMatcher::class)
        );

        return $this->filter(
            function (PaymentMethodEntity $paymentMethod) use ($channelContext) {
                if ($paymentMethod->getAvailabilityRuleId() === null) {
                    return true;
                }

                return \in_array($paymentMethod->getAvailabilityRuleId(), $channelContext->getRuleIds(), true);
            }
        );
    }

    /**
     * @return array<string>
     */
    public function getPluginIds(): array
    {
        return $this->fmap(fn (PaymentMethodEntity $paymentMethod) => $paymentMethod->getPluginId());
    }

    public function filterByPluginId(string $id): self
    {
        return $this->filter(fn (PaymentMethodEntity $paymentMethod) => $paymentMethod->getPluginId() === $id);
    }

    /**
     * Sorts the selected payment method first
     * If a different default payment method is defined, it will be sorted second
     * All other payment methods keep their respective sorting
     */
    public function sortPaymentMethodsByPreference(ChannelContext $context): void
    {
        $ids = array_merge(
            [$context->getChannel()->getPaymentMethodId()],
            $this->getIds(),
        );

        $this->sortByIdArray($ids);
    }

    public function getApiAlias(): string
    {
        return 'payment_method_collection';
    }

    protected function getExpectedClass(): string
    {
        return PaymentMethodEntity::class;
    }
}
