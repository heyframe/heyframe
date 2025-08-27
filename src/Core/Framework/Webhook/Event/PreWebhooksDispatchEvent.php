<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook\Event;

use HeyFrame\Core\Framework\Webhook\Webhook;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
class PreWebhooksDispatchEvent
{
    /**
     * @param list<Webhook> $webhooks
     */
    public function __construct(public array $webhooks)
    {
    }
}
