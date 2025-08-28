<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Notification;

use HeyFrame\Core\Framework\DataAbstractionLayer\BulkEntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Integration\IntegrationDefinition;
use HeyFrame\Core\System\User\UserDefinition;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class NotificationBulkEntityExtension extends BulkEntityExtension
{
    public function collect(): \Generator
    {
        yield IntegrationDefinition::ENTITY_NAME => [
            new OneToManyAssociationField('createdNotifications', NotificationDefinition::class, 'created_by_integration_id', 'id'),
        ];

        yield UserDefinition::ENTITY_NAME => [
            new OneToManyAssociationField('createdNotifications', NotificationDefinition::class, 'created_by_user_id', 'id'),
        ];
    }
}
