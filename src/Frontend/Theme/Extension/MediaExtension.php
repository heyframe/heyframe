<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Extension;

use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Theme\Aggregate\ThemeMediaDefinition;
use HeyFrame\Frontend\Theme\ThemeDefinition;

#[Package('framework')]
class MediaExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('themes', ThemeDefinition::class, 'preview_media_id')
        );

        $collection->add(
            (new ManyToManyAssociationField('themeMedia', ThemeDefinition::class, ThemeMediaDefinition::class, 'media_id', 'theme_id'))->addFlags(new RestrictDelete())
        );
    }

    public function getEntityName(): string
    {
        return MediaDefinition::ENTITY_NAME;
    }
}
