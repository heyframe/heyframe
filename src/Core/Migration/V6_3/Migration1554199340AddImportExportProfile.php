<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1554199340AddImportExportProfile extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1554199340;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `import_export_profile` (
              `id` binary(16) NOT NULL,
              `system_default` tinyint unsigned NOT NULL DEFAULT \'0\',
              `source_entity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `file_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `delimiter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `enclosure` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'import-export\',
              `mapping` longtext COLLATE utf8mb4_unicode_ci,
              `update_by` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `config` json DEFAULT NULL,
              `technical_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.import_export_profile.technical_name` (`technical_name`),
              CONSTRAINT `json.import_export_profile.config` CHECK (json_valid(`config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
