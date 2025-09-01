<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Tag;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerTag\CustomerTagDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTag\OrderTagDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaTag\MediaTagDefinition;
use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductTag\ProductTagDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Rule\Aggregate\RuleTag\RuleTagDefinition;
use HeyFrame\Core\Content\Rule\RuleDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@framework')]
class TagDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'tag';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return TagCollection::class;
    }

    public function getEntityClass(): string
    {
        return TagEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        $collection = new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new StringField('name', 'name'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new ApiAware()),

            // reverse side of the associations, not available in channel-api
            (new ManyToManyAssociationField('products', ProductDefinition::class, ProductTagDefinition::class, 'tag_id', 'product_id'))->addFlags(new CascadeDelete()),
            (new ManyToManyAssociationField('media', MediaDefinition::class, MediaTagDefinition::class, 'tag_id', 'media_id'))->addFlags(new CascadeDelete()),
            (new ManyToManyAssociationField('customers', CustomerDefinition::class, CustomerTagDefinition::class, 'tag_id', 'customer_id'))->addFlags(new CascadeDelete()),
            (new ManyToManyAssociationField('orders', OrderDefinition::class, OrderTagDefinition::class, 'tag_id', 'order_id'))->addFlags(new CascadeDelete()),
            (new ManyToManyAssociationField('rules', RuleDefinition::class, RuleTagDefinition::class, 'tag_id', 'rule_id'))->addFlags(new CascadeDelete()),
        ]);

        return $collection;
    }
}
