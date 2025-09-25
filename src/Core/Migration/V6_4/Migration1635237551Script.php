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
class Migration1635237551Script extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1635237551;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `script` (
              `id` binary(16) NOT NULL,
              `script` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `name` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
              `active` tinyint(1) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
