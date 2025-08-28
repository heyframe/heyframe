<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderTag;

use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Tag\TagDefinition;

#[Package('checkout')]
class OrderTagDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'order_tag';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function isVersionAware(): bool
    {
        return true;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new FkField('tag_id', 'tagId', TagDefinition::class))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('tag', 'tag_id', TagDefinition::class, 'id', false))->addFlags(new ApiAware()),
        ]);
    }
}
