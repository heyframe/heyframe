<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Aggregate\CountryState\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;

#[Package('fundamentals@discovery')]
class ChannelCountryStateDefinition extends CountryStateDefinition implements ChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
        $criteria->addFilter(
            new EqualsFilter('country_state.country.channels.id', $context->getChannelId())
        );
    }
}
