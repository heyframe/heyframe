<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook\EventLog;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BlobField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class WebhookEventLogDefinition extends EntityDefinition
{
    final public const STATUS_QUEUED = 'queued';

    final public const STATUS_RUNNING = 'running';

    final public const STATUS_FAILED = 'failed';

    final public const STATUS_SUCCESS = 'success';

    final public const ENTITY_NAME = 'webhook_event_log';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WebhookEventLogEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WebhookEventLogCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'onlyLiveVersion' => false,
        ];
    }

    public function since(): ?string
    {
        return '6.4.1.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new StringField('app_name', 'appName'),
            (new StringField('webhook_name', 'webhookName'))->addFlags(new Required()),
            (new StringField('event_name', 'eventName'))->addFlags(new Required()),
            (new StringField('delivery_status', 'deliveryStatus'))->addFlags(new Required()),
            new IntField('timestamp', 'timestamp'),
            new IntField('processing_time', 'processingTime'),
            new StringField('app_version', 'appVersion'),
            new JsonField('request_content', 'requestContent'),
            new JsonField('response_content', 'responseContent'),
            new IntField('response_status_code', 'responseStatusCode'),
            new StringField('response_reason_phrase', 'responseReasonPhrase'),
            (new StringField('url', 'url', 500))->addFlags(new Required()),
            new BoolField('only_live_version', 'onlyLiveVersion'),
            (new BlobField('serialized_webhook_message', 'serializedWebhookMessage'))->removeFlag(ApiAware::class)->addFlags(new Required(), new WriteProtected(Context::SYSTEM_SCOPE)),
            new CustomFields(),
        ]);
    }
}
