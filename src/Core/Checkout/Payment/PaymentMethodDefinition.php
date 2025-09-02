<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use HeyFrame\Core\Checkout\Payment\Aggregate\PaymentMethodTranslation\PaymentMethodTranslationDefinition;
use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Rule\RuleDefinition;
use HeyFrame\Core\Framework\App\Aggregate\AppPaymentMethod\AppPaymentMethodDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\PluginDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelPaymentMethod\ChannelPaymentMethodDefinition;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('checkout')]
class PaymentMethodDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'payment_method';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PaymentMethodCollection::class;
    }

    public function getEntityClass(): string
    {
        return PaymentMethodEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            new FkField('plugin_id', 'pluginId', PluginDefinition::class),
            new StringField('handler_identifier', 'handlerIdentifier'),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('distinguishableName'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new TranslatedField('description'))->addFlags(new ApiAware()),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new BoolField('after_order_enabled', 'afterOrderEnabled'))->addFlags(new ApiAware()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            new FkField('availability_rule_id', 'availabilityRuleId', RuleDefinition::class),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new StringField('formatted_handler_identifier', 'formattedHandlerIdentifier'))->addFlags(new WriteProtected(), new Runtime()),
            (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware(), new Required()),
            (new TranslationsAssociationField(PaymentMethodTranslationDefinition::class, 'payment_method_id'))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', false))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('availabilityRule', 'availability_rule_id', RuleDefinition::class, 'id'),

            // Reverse Associations, not available in front-api
            (new OneToManyAssociationField('channelDefaultAssignments', ChannelDefinition::class, 'payment_method_id', 'id'))->addFlags(new RestrictDelete()),
            new ManyToOneAssociationField('plugin', 'plugin_id', PluginDefinition::class, 'id'),
            (new OneToManyAssociationField('customers', CustomerDefinition::class, 'last_payment_method_id', 'id'))->addFlags(new RestrictDelete()),
            (new OneToManyAssociationField('orderTransactions', OrderTransactionDefinition::class, 'payment_method_id', 'id'))->addFlags(new RestrictDelete()),
            new ManyToManyAssociationField('channels', ChannelDefinition::class, ChannelPaymentMethodDefinition::class, 'payment_method_id', 'channel_id'),
            (new OneToOneAssociationField('appPaymentMethod', 'id', 'payment_method_id', AppPaymentMethodDefinition::class, false))->addFlags(new CascadeDelete()),

            // runtime fields
            (new StringField('short_name', 'shortName'))->addFlags(new ApiAware(), new Runtime()),
        ]);
    }
}
