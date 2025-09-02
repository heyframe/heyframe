<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerBeforeLoginEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerDeletedEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLogoutEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use HeyFrame\Core\Checkout\Order\Event\OrderPaymentMethodChangedEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
class BusinessEventRegistry
{
    /**
     * @var list<class-string>
     */
    private array $classes = [
        CustomerBeforeLoginEvent::class,
        CustomerLoginEvent::class,
        CustomerLogoutEvent::class,
        CustomerDeletedEvent::class,
        CheckoutOrderPlacedEvent::class,
        OrderPaymentMethodChangedEvent::class,
        CustomerRegisterEvent::class,
    ];

    /**
     * @param list<class-string> $classes
     */
    public function addClasses(array $classes): void
    {
        /** @var list<class-string> */
        $classes = array_unique(array_merge($this->classes, $classes));

        $this->classes = $classes;
    }

    /**
     * @return list<class-string>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
