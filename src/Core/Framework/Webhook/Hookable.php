<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook;

use HeyFrame\Core\Content\Media\Event\MediaUploadedEvent;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Event\AppActivatedEvent;
use HeyFrame\Core\Framework\App\Event\AppDeactivatedEvent;
use HeyFrame\Core\Framework\App\Event\AppDeletedEvent;
use HeyFrame\Core\Framework\App\Event\AppInstalledEvent;
use HeyFrame\Core\Framework\App\Event\AppPermissionsUpdated;
use HeyFrame\Core\Framework\App\Event\AppUpdatedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Update\Event\UpdatePostFinishEvent;
use HeyFrame\Core\System\SystemConfig\Event\SystemConfigChangedHook;

#[Package('framework')]
interface Hookable
{
    public const HOOKABLE_EVENTS = [
        MediaUploadedEvent::class => MediaUploadedEvent::EVENT_NAME,
        AppActivatedEvent::class => AppActivatedEvent::NAME,
        AppDeactivatedEvent::class => AppDeactivatedEvent::NAME,
        AppDeletedEvent::class => AppDeletedEvent::NAME,
        AppInstalledEvent::class => AppInstalledEvent::NAME,
        AppUpdatedEvent::class => AppUpdatedEvent::NAME,
        AppPermissionsUpdated::class => AppPermissionsUpdated::NAME,
        UpdatePostFinishEvent::class => UpdatePostFinishEvent::EVENT_NAME,
        SystemConfigChangedHook::class => SystemConfigChangedHook::EVENT_NAME,
    ];

    public const HOOKABLE_EVENTS_DESCRIPTION = [
        MediaUploadedEvent::class => 'Fires when a media file is uploaded',
        AppActivatedEvent::class => 'Fires when an app is activated',
        AppDeactivatedEvent::class => 'Fires when an app is deactivated',
        AppDeletedEvent::class => 'Fires when an app is deleted',
        AppInstalledEvent::class => 'Fires when an app is installed',
        AppUpdatedEvent::class => 'Fires when an app is updated',
        AppPermissionsUpdated::class => 'Fires when an apps permissions were updated with a list of the currently accepted permissions, eg after new were accepted or revoked',
        UpdatePostFinishEvent::class => 'Fires after an heyframe update has been finished',
        SystemConfigChangedHook::class => 'Fires when a system config value is changed',
    ];

    public const HOOKABLE_EVENTS_PRIVILEGES = [
        MediaUploadedEvent::class => ['media:read'],
        AppActivatedEvent::class => [],
        AppDeactivatedEvent::class => [],
        AppDeletedEvent::class => [],
        AppInstalledEvent::class => [],
        AppUpdatedEvent::class => [],
        AppPermissionsUpdated::class => [],
        UpdatePostFinishEvent::class => [],
        SystemConfigChangedHook::class => ['system_config:read'],
    ];

    public function getName(): string;

    /**
     * @return array<mixed>
     */
    public function getWebhookPayload(?AppEntity $app = null): array;

    /**
     * returns if it is allowed to dispatch the event to given app with given permissions
     */
    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool;
}
