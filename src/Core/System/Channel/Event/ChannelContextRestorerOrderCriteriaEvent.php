<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ChannelContextRestorerOrderCriteriaEvent extends NestedEvent
{
    public function __construct(
        protected Criteria $criteria,
        protected Context $context
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }
}
