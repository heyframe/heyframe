<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1554200141ImportExportFile extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1554200141;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `import_export_file` (
              `id` binary(16) NOT NULL,
              `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `expire_date` datetime(3) NOT NULL,
              `size` int DEFAULT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `access_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
