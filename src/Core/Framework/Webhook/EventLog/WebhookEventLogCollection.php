<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook\EventLog;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WebhookEventLogEntity>
 */
#[Package('framework')]
class WebhookEventLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WebhookEventLogEntity::class;
    }
}
