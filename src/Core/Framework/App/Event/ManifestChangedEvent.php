<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
abstract class ManifestChangedEvent extends AppChangedEvent
{
    public const LIFECYCLE_EVENTS = [
        AppActivatedEvent::NAME,
        AppDeactivatedEvent::NAME,
        AppDeletedEvent::NAME,
        AppInstalledEvent::NAME,
        AppUpdatedEvent::NAME,
    ];

    public function __construct(
        AppEntity $app,
        private readonly Manifest $manifest,
        Context $context
    ) {
        parent::__construct($app, $context);
    }

    abstract public function getName(): string;

    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    public function getWebhookPayload(?AppEntity $app = null): array
    {
        return [
            'appVersion' => $this->manifest->getMetadata()->getVersion(),
        ];
    }
}
