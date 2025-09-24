<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App;

use HeyFrame\Core\Framework\Api\Acl\Admin\Role\AclRoleDefinition;
use HeyFrame\Core\Framework\App\Aggregate\ActionButton\ActionButtonDefinition;
use HeyFrame\Core\Framework\App\Aggregate\AppPaymentMethod\AppPaymentMethodDefinition;
use HeyFrame\Core\Framework\App\Aggregate\AppScriptCondition\AppScriptConditionDefinition;
use HeyFrame\Core\Framework\App\Aggregate\AppTranslation\AppTranslationDefinition;
use HeyFrame\Core\Framework\App\Aggregate\FlowAction\AppFlowActionDefinition;
use HeyFrame\Core\Framework\App\Aggregate\FlowEvent\AppFlowEventDefinition;
use HeyFrame\Core\Framework\App\Template\TemplateDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BlobField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Since;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\ScriptDefinition;
use HeyFrame\Core\Framework\Webhook\WebhookDefinition;
use HeyFrame\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use HeyFrame\Core\System\Integration\IntegrationDefinition;

/**
 * @internal
 */
#[Package('framework')]
class AppDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'app';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AppEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AppCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false,
            'configurable' => false,
            'allowDisable' => true,
            'modules' => [],
            'cookies' => [],
            'allowedHosts' => [],
            'templateLoadPriority' => 0,
            'sourceType' => 'local',
            'selfManaged' => false,
            'requestedPrivileges' => [],
        ];
    }

    public function since(): ?string
    {
        return '6.3.1.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new StringField('path', 'path', 4096))->addFlags(new Required()),
            new StringField('author', 'author'),
            new StringField('copyright', 'copyright'),
            new StringField('license', 'license'),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            (new BoolField('configurable', 'configurable'))->addFlags(new Required()),
            new StringField('privacy', 'privacy'),
            (new StringField('version', 'version'))->addFlags(new Required()),
            (new BlobField('icon', 'iconRaw'))->removeFlag(ApiAware::class),
            (new StringField('icon', 'icon'))->addFlags(new WriteProtected(), new Runtime()),
            (new StringField('app_secret', 'appSecret'))->removeFlag(ApiAware::class)->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            new ListField('modules', 'modules', JsonField::class),
            new JsonField('main_module', 'mainModule'),
            new ListField('cookies', 'cookies', JsonField::class),
            (new BoolField('allow_disable', 'allowDisable'))->addFlags(new Required()),
            new StringField('base_app_url', 'baseAppUrl', 1024),
            new ListField('allowed_hosts', 'allowedHosts', StringField::class),
            new IntField('template_load_priority', 'templateLoadPriority'),
            new StringField('checkout_gateway_url', 'checkoutGatewayUrl'),
            new StringField('context_gateway_url', 'contextGatewayUrl'),
            new StringField('in_app_purchases_gateway_url', 'inAppPurchasesGatewayUrl'),
            new StringField('source_type', 'sourceType'),
            new JsonField('source_config', 'sourceConfig'),
            new BoolField('self_managed', 'selfManaged'),
            (new ListField('requested_privileges', 'requestedPrivileges', StringField::class))->addFlags(new Required()),

            (new TranslationsAssociationField(AppTranslationDefinition::class, 'app_id'))->addFlags(new Required(), new CascadeDelete()),
            new TranslatedField('label'),
            new TranslatedField('description'),
            new TranslatedField('privacyPolicyExtensions'),
            (new TranslatedField('customFields'))->addFlags(new Since('6.4.1.0')),

            (new FkField('integration_id', 'integrationId', IntegrationDefinition::class))->addFlags(new Required()),
            new OneToOneAssociationField('integration', 'integration_id', 'id', IntegrationDefinition::class),

            (new FkField('acl_role_id', 'aclRoleId', AclRoleDefinition::class))->addFlags(new Required()),
            new OneToOneAssociationField('aclRole', 'acl_role_id', 'id', AclRoleDefinition::class),

            (new OneToManyAssociationField('customFieldSets', CustomFieldSetDefinition::class, 'app_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('actionButtons', ActionButtonDefinition::class, 'app_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('templates', TemplateDefinition::class, 'app_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('scripts', ScriptDefinition::class, 'app_id'))->addFlags(new CascadeDelete())->removeFlag(ApiAware::class),
            (new OneToManyAssociationField('webhooks', WebhookDefinition::class, 'app_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('paymentMethods', AppPaymentMethodDefinition::class, 'app_id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('scriptConditions', AppScriptConditionDefinition::class, 'app_id'))->addFlags(new CascadeDelete())->removeFlag(ApiAware::class),
            (new OneToManyAssociationField('flowActions', AppFlowActionDefinition::class, 'app_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('flowEvents', AppFlowEventDefinition::class, 'app_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
