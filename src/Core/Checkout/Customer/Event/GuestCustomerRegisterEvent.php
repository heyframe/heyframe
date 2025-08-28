<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Event;

use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class GuestCustomerRegisterEvent extends CustomerRegisterEvent implements FlowEventAware
{
    final public const EVENT_NAME = 'checkout.customer.guest_register';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }
}
