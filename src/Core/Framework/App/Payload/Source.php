<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Payload;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\CloneTrait;
use HeyFrame\Core\Framework\Struct\JsonSerializableTrait;

/**
 * @internal only for use by the app-system
 *
 * @method array{url: string, instanceId: string, appVersion: string} jsonSerialize()
 */
#[Package('framework')]
class Source implements \JsonSerializable
{
    use CloneTrait;
    use JsonSerializableTrait;

    public function __construct(
        protected string $url,
        protected string $instanceId,
        protected string $appVersion,
        protected ?string $inAppPurchases = null,
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function getAppVersion(): string
    {
        return $this->appVersion;
    }

    public function getInAppPurchases(): ?string
    {
        return $this->inAppPurchases;
    }
}
