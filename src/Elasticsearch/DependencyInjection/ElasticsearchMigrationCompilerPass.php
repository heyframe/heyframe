<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\DependencyInjection;

use HeyFrame\Core\Framework\DependencyInjection\CompilerPass\AbstractMigrationReplacementCompilerPass;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ElasticsearchMigrationCompilerPass extends AbstractMigrationReplacementCompilerPass
{
    protected function getMigrationPath(): string
    {
        return \dirname(__DIR__);
    }

    protected function getMigrationNamespacePart(): string
    {
        return 'Elasticsearch';
    }
}
