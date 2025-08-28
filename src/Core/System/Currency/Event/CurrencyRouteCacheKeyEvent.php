<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency\Event;

use HeyFrame\Core\Framework\Adapter\Cache\StoreApiRouteCacheKeyEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@framework')]
/**
 * @deprecated tag:v6.8.0 - Will be removed in 6.8.0 as it was not used anymore
 */
class CurrencyRouteCacheKeyEvent extends StoreApiRouteCacheKeyEvent
{
}
