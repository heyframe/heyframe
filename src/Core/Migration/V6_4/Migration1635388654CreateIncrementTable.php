<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1635388654CreateIncrementTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1635388654;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `increment` (
              `pool` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `cluster` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `count` bigint unsigned NOT NULL DEFAULT \'1\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`pool`,`cluster`,`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
