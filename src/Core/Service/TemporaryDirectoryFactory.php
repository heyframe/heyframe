<?php declare(strict_types=1);

namespace HeyFrame\Core\Service;

use HeyFrame\Core\Framework\App\Source\TemporaryDirectoryFactory as CoreTemporaryDirectoryFactory;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Filesystem\Path;

/**
 * @internal
 */
#[Package('framework')]
class TemporaryDirectoryFactory extends CoreTemporaryDirectoryFactory
{
    public function __construct(private readonly string $projectDirectory)
    {
    }

    public function path(): string
    {
        return Path::join($this->projectDirectory, 'var/services');
    }
}
