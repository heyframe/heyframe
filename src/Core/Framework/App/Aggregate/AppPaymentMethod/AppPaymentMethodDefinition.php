<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\AppPaymentMethod;

use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Framework\App\AppDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppPaymentMethodDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'app_payment_method';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return AppPaymentMethodCollection::class;
    }

    public function getEntityClass(): string
    {
        return AppPaymentMethodEntity::class;
    }

    public function since(): ?string
    {
        return '6.4.1.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('app_name', 'appName'))->addFlags(new Required()),
            (new StringField('identifier', 'identifier'))->addFlags(new Required()),
            new StringField('pay_url', 'payUrl'),
            new StringField('finalize_url', 'finalizeUrl'),
            new StringField('validate_url', 'validateUrl'),
            new StringField('capture_url', 'captureUrl'),
            new StringField('refund_url', 'refundUrl'),
            new StringField('recurring_url', 'recurringUrl'),

            new FkField('app_id', 'appId', AppDefinition::class),
            new ManyToOneAssociationField('app', 'app_id', AppDefinition::class),

            new FkField('original_media_id', 'originalMediaId', MediaDefinition::class),
            new ManyToOneAssociationField('originalMedia', 'original_media_id', MediaDefinition::class),

            (new FkField('payment_method_id', 'paymentMethodId', PaymentMethodDefinition::class))->addFlags(new Required()),
            new OneToOneAssociationField('paymentMethod', 'payment_method_id', 'id', PaymentMethodDefinition::class, false),
        ]);
    }
}
