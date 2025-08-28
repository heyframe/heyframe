<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @deprecated tag:v6.8.0 - reason:remove-entity - Will be removed
 */
#[Package('fundamentals@after-sales')]
class ImportExportProfileTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = ImportExportProfileDefinition::ENTITY_NAME . '_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ImportExportProfileTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ImportExportProfileTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.2.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ImportExportProfileDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new StringField('label', 'label'),
        ]);
    }
}
