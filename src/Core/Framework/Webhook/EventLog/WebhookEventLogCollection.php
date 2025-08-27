<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook\EventLog;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<WebhookEventLogEntity>
 */
class WebhookEventLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WebhookEventLogEntity::class;
    }
}
