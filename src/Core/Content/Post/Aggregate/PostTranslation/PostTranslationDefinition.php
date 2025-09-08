<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Post\Aggregate\PostTranslation;

use HeyFrame\Core\Content\Post\PostDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

class PostTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'post_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PostTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return PostTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return PostDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
        ]);
    }
}
