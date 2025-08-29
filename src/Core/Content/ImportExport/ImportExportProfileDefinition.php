<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport;

use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
class ImportExportProfileDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'import_export_profile';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ImportExportProfileEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        $fields = new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('technical_name', 'technicalName'))->addFlags(new Required()),
            new StringField('type', 'type'),
            new BoolField('system_default', 'systemDefault'),
            (new StringField('source_entity', 'sourceEntity'))->addFlags(new Required()),
            (new StringField('file_type', 'fileType'))->addFlags(new Required()),
            (new StringField('delimiter', 'delimiter'))->addFlags(new Required()),
            (new StringField('enclosure', 'enclosure'))->addFlags(new Required()),
            new JsonField('mapping', 'mapping', [], []),
            new JsonField('update_by', 'updateBy', [], []),
            new JsonField('config', 'config', [], []),
            (new OneToManyAssociationField('importExportLogs', ImportExportLogDefinition::class, 'profile_id'))->addFlags(new SetNullOnDelete()),
        ]);

        return $fields;
    }
}
