<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\SeoUrl\Channel;

use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
class ChannelSeoUrlDefinition extends SeoUrlDefinition implements ChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
        $criteria->addFilter(new EqualsFilter('languageId', $context->getLanguageId()));
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('channelId', $context->getChannelId()),
            new EqualsFilter('channelId', null),
        ]));
        $criteria->addFilter(new EqualsFilter('isCanonical', true));
        $criteria->addFilter(new EqualsFilter('isDeleted', false));
    }
}
