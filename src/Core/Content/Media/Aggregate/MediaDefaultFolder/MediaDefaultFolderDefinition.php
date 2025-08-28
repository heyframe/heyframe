<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Aggregate\MediaDefaultFolder;

use HeyFrame\Core\Content\Media\Aggregate\MediaFolder\MediaFolderDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaDefaultFolderDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'media_default_folder';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return MediaDefaultFolderCollection::class;
    }

    public function getEntityClass(): string
    {
        return MediaDefaultFolderEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new StringField('entity', 'entity'))->addFlags(new Required()),
            new OneToOneAssociationField('folder', 'id', 'default_folder_id', MediaFolderDefinition::class),
            new CustomFields(),
        ]);
    }
}
