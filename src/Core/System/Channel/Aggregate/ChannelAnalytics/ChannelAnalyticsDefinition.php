<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Aggregate\ChannelAnalytics;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('discovery')]
class ChannelAnalyticsDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'channel_analytics';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ChannelAnalyticsCollection::class;
    }

    public function getEntityClass(): string
    {
        return ChannelAnalyticsEntity::class;
    }

    public function since(): ?string
    {
        return '6.2.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new StringField('tracking_id', 'trackingId'),
            new BoolField('active', 'active'),
            new BoolField('track_orders', 'trackOrders'),
            new BoolField('anonymize_ip', 'anonymizeIp'),
            new OneToOneAssociationField('channel', 'id', 'analytics_id', ChannelDefinition::class, false),
        ]);
    }
}
