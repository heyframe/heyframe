<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Extension;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Frontend\Theme\Aggregate\ThemeChannelDefinition;
use HeyFrame\Frontend\Theme\ThemeDefinition;

#[Package('framework')]
class ChannelExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new ManyToManyAssociationField('themes', ThemeDefinition::class, ThemeChannelDefinition::class, 'channel_id', 'theme_id')
        );
    }

    public function getEntityName(): string
    {
        return ChannelDefinition::ENTITY_NAME;
    }
}
