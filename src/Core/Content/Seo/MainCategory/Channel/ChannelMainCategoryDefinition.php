<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\MainCategory\Channel;

use HeyFrame\Core\Content\Seo\MainCategory\MainCategoryDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;

#[Package('inventory')]
class ChannelMainCategoryDefinition extends MainCategoryDefinition implements ChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
        $criteria->addFilter(new EqualsFilter('channelId', $context->getChannelId()));
    }
}
