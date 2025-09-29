<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;

#[Package('discovery')]
class ChannelNavigationDefinition extends NavigationDefinition implements ChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
    }

    public function getEntityClass(): string
    {
        return ChannelNavigationEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        $fields = parent::defineFields();

        $fields->add(
            (new StringField('seo_url', 'seoUrl'))->addFlags(new ApiAware(), new Runtime(['type', 'linkType', 'internalLink']))
        );

        return $fields;
    }
}
