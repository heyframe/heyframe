<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class InstanceService
{
    public function __construct(
        private readonly string $heyframeVersion,
        private readonly ?string $instanceId
    ) {
    }

    public function getHeyFrameVersion(): string
    {
        if (str_ends_with($this->heyframeVersion, '-dev')) {
            return '___VERSION___';
        }

        return $this->heyframeVersion;
    }

    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }
}
