<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Source;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Filesystem\Path;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
#[Package('framework')]
class TemporaryDirectoryFactory
{
    public function path(): string
    {
        return Path::join(sys_get_temp_dir(), 'apps');
    }
}
