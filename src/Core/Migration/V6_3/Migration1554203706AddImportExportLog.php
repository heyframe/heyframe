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
class Migration1554203706AddImportExportLog extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1554203706;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `import_export_log` (
              `id` binary(16) NOT NULL,
              `activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `records` int NOT NULL,
              `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `profile_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `user_id` binary(16) DEFAULT NULL,
              `profile_id` binary(16) DEFAULT NULL,
              `file_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `config` json DEFAULT NULL,
              `result` json DEFAULT NULL,
              `invalid_records_log_id` binary(16) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.import_export_log.user_id` (`user_id`),
              KEY `fk.import_export_log.profile_id` (`profile_id`),
              KEY `fk.import_export_log.invalid_records_log_id` (`invalid_records_log_id`),
              KEY `fk.import_export_log.file_id` (`file_id`),
              CONSTRAINT `fk.import_export_log.file_id` FOREIGN KEY (`file_id`) REFERENCES `import_export_file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.import_export_log.invalid_records_log_id` FOREIGN KEY (`invalid_records_log_id`) REFERENCES `import_export_log` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.import_export_log.profile_id` FOREIGN KEY (`profile_id`) REFERENCES `import_export_profile` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.import_export_log.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
              CONSTRAINT `json.import_export_log.config` CHECK (json_valid(`config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
