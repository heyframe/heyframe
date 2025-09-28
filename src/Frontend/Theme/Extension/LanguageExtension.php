<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Extension;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Language\LanguageDefinition;
use HeyFrame\Frontend\Theme\Aggregate\ThemeTranslationDefinition;

#[Package('framework')]
class LanguageExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('themeTranslations', ThemeTranslationDefinition::class, 'language_id'))->addFlags(new CascadeDelete())
        );
    }

    public function getEntityName(): string
    {
        return LanguageDefinition::ENTITY_NAME;
    }
}
