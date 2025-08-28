<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Source;

use HeyFrame\Core\Framework\App\ActiveAppsLoader;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Filesystem;

/**
 * @internal
 */
#[Package('framework')]
class NoDatabaseSourceResolver
{
    public function __construct(private readonly ActiveAppsLoader $activeAppsLoader)
    {
    }

    public function filesystem(string $appName): Filesystem
    {
        foreach ($this->activeAppsLoader->getActiveApps() as $activeApp) {
            if ($activeApp['name'] === $appName) {
                return new Filesystem($activeApp['path']);
            }
        }

        throw AppException::notFoundByField($appName, 'name');
    }
}
