<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class IsFlowEventAware
{
}
