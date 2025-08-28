<?php

declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 *
 * This event can be used to react to the creation of a new context.
 * It must be used very carefully, as it practically effects every part of HeyFrame.
 */
#[Package('framework')]
final class ContextCreatedEvent
{
    public function __construct(
        public Context $context,
    ) {
    }
}
