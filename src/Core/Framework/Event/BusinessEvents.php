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
final class BusinessEvents
{
    public const CHECKOUT_CUSTOMER_BEFORE_LOGIN = CustomerBeforeLoginEvent::EVENT_NAME;

    public const CHECKOUT_CUSTOMER_LOGIN = CustomerLoginEvent::EVENT_NAME;

    public const CHECKOUT_CUSTOMER_LOGOUT = CustomerLogoutEvent::EVENT_NAME;

    public const CHECKOUT_CUSTOMER_DELETED = CustomerDeletedEvent::EVENT_NAME;

    public const CHECKOUT_ORDER_PLACED = CheckoutOrderPlacedEvent::EVENT_NAME;

    public const CHECKOUT_ORDER_PAYMENT_METHOD_CHANGED = OrderPaymentMethodChangedEvent::EVENT_NAME;

    public const CUSTOMER_REGISTER = CustomerRegisterEvent::EVENT_NAME;

    private function __construct()
    {
    }
}
