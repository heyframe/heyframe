<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Aggregate\FlowTemplate;

use HeyFrame\Core\Content\Flow\DataAbstractionLayer\Field\FlowTemplateConfigField;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

class FlowTemplateDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'flow_template';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return FlowTemplateCollection::class;
    }

    public function getEntityClass(): string
    {
        return FlowTemplateEntity::class;
    }

    public function since(): ?string
    {
        return '6.4.18.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name', 255))->addFlags(new Required()),
            new FlowTemplateConfigField('config', 'config'),
        ]);
    }
}
