<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Delta;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
abstract class AbstractAppDeltaProvider
{
    abstract public function getDeltaName(): string;

    /**
     * @return array<array-key, mixed>
     */
    abstract public function getReport(Manifest $manifest, AppEntity $app): array;

    abstract public function hasDelta(Manifest $manifest, AppEntity $app): bool;
}
