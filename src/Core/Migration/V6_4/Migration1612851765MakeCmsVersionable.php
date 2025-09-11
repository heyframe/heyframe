<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MakeVersionableMigrationHelper;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1612851765MakeCmsVersionable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1612851765;
    }

    public function update(Connection $connection): void
    {
        $versionableMigrationHelper = new MakeVersionableMigrationHelper($connection);

        $tables = [
            'cms_page',
        ];

        foreach ($tables as $table) {
            $hydratedData = $versionableMigrationHelper->getRelationData($table, 'id');
            $playbook = $versionableMigrationHelper->createSql($hydratedData, $table, 'version_id', Defaults::LIVE_VERSION);

            foreach ($playbook as $query) {
                $connection->executeStatement($query);
            }
        }
    }
}
