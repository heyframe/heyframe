<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\InstanceId;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
#[Package('framework')]
class InstanceIdDeletedEvent extends Event
{
}
