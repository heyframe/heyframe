<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet;

use HeyFrame\Core\Checkout\Wallet\Aggregate\WalletTransactions\WalletTransactionsDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ExtraFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Currency\CurrencyDefinition;

#[Package('checkout')]
class WalletDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'wallet';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return WalletCollection::class;
    }

    public function getEntityClass(): string
    {
        return WalletEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('currency_id', 'currencyId', CurrencyDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FloatField('balance', 'balance'))->addFlags(new ApiAware(), new WriteProtected()),
            (new FloatField('frozen_balance', 'frozenBalance'))->addFlags(new ApiAware(), new WriteProtected()),
            (new FloatField('bonus_balance', 'bonusBalance'))->addFlags(new ApiAware(), new WriteProtected()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new StringField('identifier', 'identifier'))->addFlags(new ApiAware(), new Required()),
            (new StringField('referenced_id', 'referencedId'))->addFlags(new ApiAware(), new Required()),
            (new CustomFields())->addFlags(new ApiAware()),
            (new ExtraFields())->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('currency', 'currency_id', CurrencyDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('transactions', WalletTransactionsDefinition::class, 'wallet_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete()),
        ]);
    }
}
