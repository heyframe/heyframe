<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Kernel;

/**
 * @internal
 */
#[Package('framework')]
class StaticAnalyzeKernel extends Kernel
{
    public function getCacheDir(): string
    {
        return \sprintf(
            '%s/var/cache/static_%s',
            $this->getProjectDir(),
            $this->getEnvironment(),
        );
    }
}
