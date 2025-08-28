<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;
use HeyFrame\Core\System\Currency\CurrencyDefinition;

#[Package('fundamentals@framework')]
class ChannelCurrencyDefinition extends CurrencyDefinition implements ChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
        $criteria->addFilter(new EqualsFilter('currency.channels.id', $context->getChannelId()));
    }
}
