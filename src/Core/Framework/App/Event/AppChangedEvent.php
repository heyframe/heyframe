<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Webhook\AclPrivilegeCollection;
use HeyFrame\Core\Framework\Webhook\Hookable;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
abstract class AppChangedEvent extends Event implements HeyFrameEvent, Hookable
{
    public function __construct(
        private readonly AppEntity $app,
        private readonly Context $context
    ) {
    }

    abstract public function getName(): string;

    public function getApp(): AppEntity
    {
        return $this->app;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getWebhookPayload(?AppEntity $app = null): array
    {
        return [];
    }

    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool
    {
        return $appId === $this->app->getId();
    }
}
