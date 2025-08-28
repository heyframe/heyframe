<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Event;

use HeyFrame\Core\Framework\Adapter\Cache\StoreApiRouteCacheKeyEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
/**
 * @deprecated tag:v6.8.0 - Will be removed in 6.8.0 as it was not used anymore
 */
class PaymentMethodRouteCacheKeyEvent extends StoreApiRouteCacheKeyEvent
{
}
