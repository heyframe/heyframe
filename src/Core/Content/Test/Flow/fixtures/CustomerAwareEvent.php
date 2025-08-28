<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test\Flow\fixtures;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('after-sales')]
class CustomerAwareEvent implements CustomerAware, FlowEventAware
{
    public function __construct(
        protected string $customerId,
        protected ?Context $context = null
    ) {
    }

    public static function getAvailableData(): EventDataCollection
    {
        return new EventDataCollection();
    }

    public function getName(): string
    {
        return 'customer.aware.event';
    }

    public function getContext(): Context
    {
        return $this->context ?? Context::createDefaultContext();
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
