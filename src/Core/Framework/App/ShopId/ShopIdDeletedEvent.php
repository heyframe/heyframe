<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ShopId;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
#[Package('framework')]
class ShopIdDeletedEvent extends Event
{
}
