<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefundPosition\OrderTransactionCaptureRefundPositionDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Promotion\PromotionDefinition;
use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CalculatedPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Computed;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\PriceDefinitionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderLineItemDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_line_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderLineItemCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderLineItemEntity::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return ['position' => 1];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): ?string
    {
        return OrderDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),

            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('promotion_id', 'promotionId', PromotionDefinition::class))->addFlags(new ApiAware()),
            (new ParentFkField(self::class))->addFlags(new ApiAware()),
            (new ReferenceVersionField(self::class, 'parent_version_id'))->addFlags(new ApiAware(), new Required()),
            (new FkField('cover_id', 'coverId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('cover', 'cover_id', MediaDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new StringField('identifier', 'identifier'))->addFlags(new ApiAware(), new Required()),
            (new StringField('referenced_id', 'referencedId'))->addFlags(new ApiAware()),
            (new IntField('quantity', 'quantity'))->addFlags(new ApiAware(), new Required()),
            (new StringField('label', 'label'))->addFlags(new ApiAware(), new Required()),
            (new JsonField('payload', 'payload'))->addFlags(new ApiAware()),
            (new BoolField('good', 'good'))->addFlags(new ApiAware()),
            (new BoolField('removable', 'removable'))->addFlags(new ApiAware()),
            (new BoolField('stackable', 'stackable'))->addFlags(new ApiAware()),
            (new IntField('position', 'position'))->addFlags(new ApiAware(), new Required()),
            (new ListField('states', 'states', StringField::class))->addFlags(new ApiAware(), new Required()),

            (new CalculatedPriceField('price', 'price'))->addFlags(new Required()),
            (new PriceDefinitionField('price_definition', 'priceDefinition'))->addFlags(new ApiAware()),

            (new FloatField('unit_price', 'unitPrice'))->addFlags(new ApiAware(), new Computed()),
            (new FloatField('total_price', 'totalPrice'))->addFlags(new ApiAware(), new Computed()),
            (new LongTextField('description', 'description'))->addFlags(new ApiAware()),
            (new StringField('type', 'type'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
            new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id', false),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('promotion', 'promotion_id', PromotionDefinition::class, 'id', false),
            (new OneToManyAssociationField('orderTransactionCaptureRefundPositions', OrderTransactionCaptureRefundPositionDefinition::class, 'order_line_item_id'))->addFlags(new CascadeDelete()),
            (new ParentAssociationField(self::class))->addFlags(new ApiAware()),
            (new ChildrenAssociationField(self::class))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}
