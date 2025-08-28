<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Language\LanguageDefinition;

/**
 * @internal
 */
#[Package('inventory')]
class ExtendedProductDefinition extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'extended_product';
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new Required(), new PrimaryKey()),
            new StringField('name', 'name'),
            new FkField('product_id', 'productId', ProductDefinition::class),
            new FkField('language_id', 'languageId', LanguageDefinition::class),
            new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class, 'id', false),
            new OneToOneAssociationField('toOne', 'product_id', 'id', ProductDefinition::class),
            new ManyToOneAssociationField('manyToOne', 'product_id', ProductDefinition::class, 'id'),
        ]);
    }
}
