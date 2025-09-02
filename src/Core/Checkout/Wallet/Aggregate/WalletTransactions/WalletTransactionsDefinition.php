<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Aggregate\WalletTransactions;

use HeyFrame\Core\Checkout\Wallet\WalletDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class WalletTransactionsDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'wallet_transactions';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return WalletTransactionsCollection::class;
    }

    public function getEntityClass(): string
    {
        return WalletTransactionsEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return WalletDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('wallet_id', 'walletId', WalletDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new StringField('tx_type', 'txType'))->addFlags(new ApiAware(), new Required()),
            (new FloatField('amount', 'amount'))->addFlags(new ApiAware(), new WriteProtected()),
            (new FloatField('balance_after', 'balanceAfter'))->addFlags(new ApiAware(), new Required(), new WriteProtected()),
            (new StringField('referenced_id', 'referencedId'))->addFlags(new ApiAware()),
            (new StringField('reference_type', 'referenceType'))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('wallet', 'wallet_id', WalletDefinition::class, 'id', false),
        ]);
    }
}
