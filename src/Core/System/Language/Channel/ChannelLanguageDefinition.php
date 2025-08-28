<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Language\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;
use HeyFrame\Core\System\Language\LanguageDefinition;

#[Package('fundamentals@discovery')]
class ChannelLanguageDefinition extends LanguageDefinition implements ChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
        $criteria->addFilter(new EqualsFilter('language.channels.id', $context->getChannelId()));
    }
}
