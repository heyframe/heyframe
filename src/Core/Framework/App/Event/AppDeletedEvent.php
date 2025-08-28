<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Webhook\AclPrivilegeCollection;
use HeyFrame\Core\Framework\Webhook\Hookable;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class AppDeletedEvent extends Event implements HeyFrameEvent, Hookable
{
    final public const NAME = 'app.deleted';

    public function __construct(
        private readonly string $appId,
        private readonly Context $context,
        private readonly bool $keepUserData = false
    ) {
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function keepUserData(): bool
    {
        return $this->keepUserData;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getWebhookPayload(?AppEntity $app = null): array
    {
        return [
            'keepUserData' => $this->keepUserData,
        ];
    }

    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool
    {
        return $appId === $this->getAppId();
    }
}
