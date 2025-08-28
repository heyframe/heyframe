<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Address;

use HeyFrame\Core\Checkout\Cart\Address\Error\BillingAddressCountryRegionMissingError;
use HeyFrame\Core\Checkout\Cart\Address\Error\BillingAddressSalutationMissingError;
use HeyFrame\Core\Checkout\Cart\Address\Error\ShippingAddressBlockedError;
use HeyFrame\Core\Checkout\Cart\Address\Error\ShippingAddressCountryRegionMissingError;
use HeyFrame\Core\Checkout\Cart\Address\Error\ShippingAddressSalutationMissingError;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartValidatorInterface;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Content\Product\State;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\Service\ResetInterface;

#[Package('checkout')]
class AddressValidator implements CartValidatorInterface, ResetInterface
{
    /**
     * @var array<string, bool>
     */
    private array $available = [];

    /**
     * @internal
     *
     * @param EntityRepository<EntityCollection<Entity>> $channelCountryRepository
     */
    public function __construct(private readonly EntityRepository $channelCountryRepository)
    {
    }

    public function validate(Cart $cart, ErrorCollection $errors, ChannelContext $context): void
    {
        $country = $context->getShippingLocation()->getCountry();
        $customer = $context->getCustomer();
        $validateShipping = $cart->getLineItems()->count() === 0
            || $cart->getLineItems()->hasLineItemWithState(State::IS_PHYSICAL);

        if (!$country->getActive() && $validateShipping) {
            $errors->add(new ShippingAddressBlockedError((string) $country->getTranslation('name')));

            return;
        }

        if (!$country->getShippingAvailable() && $validateShipping) {
            $errors->add(new ShippingAddressBlockedError((string) $country->getTranslation('name')));

            return;
        }

        if (!$this->isChannelCountry($country->getId(), $context) && $validateShipping) {
            $errors->add(new ShippingAddressBlockedError((string) $country->getTranslation('name')));

            return;
        }

        if ($customer === null) {
            return;
        }

        if ($customer->getActiveBillingAddress() === null || $customer->getActiveShippingAddress() === null) {
            // No need to add salutation-specific errors in this case
            return;
        }

        if (!$customer->getActiveBillingAddress()->getSalutationId()) {
            $errors->add(new BillingAddressSalutationMissingError($customer->getActiveBillingAddress()));

            return;
        }

        if (!$customer->getActiveShippingAddress()->getSalutationId() && $validateShipping) {
            $errors->add(new ShippingAddressSalutationMissingError($customer->getActiveShippingAddress()));
        }

        if ($customer->getActiveBillingAddress()->getCountry()?->getForceStateInRegistration()) {
            if (!$customer->getActiveBillingAddress()->getCountryState()) {
                $errors->add(new BillingAddressCountryRegionMissingError($customer->getActiveBillingAddress()));
            }
        }

        if ($customer->getActiveShippingAddress()->getCountry()?->getForceStateInRegistration()) {
            if (!$customer->getActiveShippingAddress()->getCountryState()) {
                $errors->add(new ShippingAddressCountryRegionMissingError($customer->getActiveShippingAddress()));
            }
        }
    }

    public function reset(): void
    {
        $this->available = [];
    }

    private function isChannelCountry(string $countryId, ChannelContext $context): bool
    {
        if (isset($this->available[$countryId])) {
            return $this->available[$countryId];
        }

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('channelId', $context->getChannelId()))
            ->addFilter(new EqualsFilter('countryId', $countryId));

        return $this->available[$countryId] = $this->channelCountryRepository->searchIds($criteria, $context->getContext())->getTotal() !== 0;
    }
}
