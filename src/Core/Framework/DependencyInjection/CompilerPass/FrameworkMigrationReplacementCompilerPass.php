<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class FrameworkMigrationReplacementCompilerPass extends AbstractMigrationReplacementCompilerPass
{
    protected function getMigrationPath(): string
    {
        return \dirname(__DIR__, 3);
    }

    protected function getMigrationNamespacePart(): string
    {
        return 'Core';
    }
}
