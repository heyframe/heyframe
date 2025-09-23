<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1570187167AddedAppConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1570187167;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `app_config` (
              `key` varchar(50) NOT NULL,
              `value` LONGTEXT NOT NULL,
               PRIMARY KEY (`key`)
            );
        ');

        $connection->insert('app_config', [
            '`key`' => 'cache-id',
            '`value`' => Uuid::randomHex(),
        ]);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
