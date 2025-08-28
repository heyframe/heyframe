<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
readonly class ServiceInstalledEvent implements HeyFrameEvent
{
    public function __construct(public string $service, private Context $context)
    {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
