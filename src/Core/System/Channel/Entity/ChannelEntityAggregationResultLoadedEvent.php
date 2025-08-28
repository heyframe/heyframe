<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Entity;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityAggregationResultLoadedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
class ChannelEntityAggregationResultLoadedEvent extends EntityAggregationResultLoadedEvent implements HeyFrameChannelEvent
{
    private readonly ChannelContext $channelContext;

    public function __construct(
        EntityDefinition $definition,
        AggregationResultCollection $result,
        ChannelContext $channelContext
    ) {
        parent::__construct($definition, $result, $channelContext->getContext());
        $this->channelContext = $channelContext;
    }

    public function getName(): string
    {
        return 'channel.' . parent::getName();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
