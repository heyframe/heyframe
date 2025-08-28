<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test\Flow\fixtures;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('after-sales')]
class RawFlowEvent implements FlowEventAware
{
    public function __construct(protected ?Context $context = null)
    {
    }

    public static function getAvailableData(): EventDataCollection
    {
        return new EventDataCollection();
    }

    public function getName(): string
    {
        return 'raw_flow.event';
    }

    public function getContext(): Context
    {
        return $this->context ?? Context::createDefaultContext();
    }
}
