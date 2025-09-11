<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\LandingPage\Channel;

use HeyFrame\Core\Content\LandingPage\LandingPageDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;

#[Package('discovery')]
class ChannelLandingPageDefinition extends LandingPageDefinition implements ChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
    }
}
