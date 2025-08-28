<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Permission\PermissionsConsent;

/**
 * @internal
 */
#[Package('framework')]
readonly class PermissionsGrantedEvent implements HeyFrameEvent
{
    public function __construct(
        public PermissionsConsent $permissionsConsent,
        public Context $context,
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
