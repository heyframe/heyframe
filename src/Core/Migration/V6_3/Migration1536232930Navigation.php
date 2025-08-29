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
class Migration1536232930Navigation extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232930;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `navigation` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `parent_id` BINARY(16) NULL,
              `parent_version_id` BINARY(16) NULL,
              `path` LONGTEXT COLLATE utf8mb4_unicode_ci,
              `level` INT(11) unsigned NOT NULL DEFAULT \'1\',
              `child_count` INT(11) unsigned NOT NULL DEFAULT \'0\',
              `active` tinyint(1) NOT NULL DEFAULT \'1\',
              `after_navigation_id` binary(16) DEFAULT NULL,
              `after_navigation_version_id` binary(16) DEFAULT NULL,
              `visible` tinyint unsigned NOT NULL DEFAULT \'1\',
              `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3),
              PRIMARY KEY (`id`, `version_id`),
              KEY `idx.navigation.level` (`level`),
              KEY `fk.navigation.after_navigation_id` (`after_navigation_id`,`after_navigation_version_id`),
              CONSTRAINT `fk.navigation.parent_id` FOREIGN KEY (`parent_id`, `parent_version_id`)
                REFERENCES `navigation` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `navigation_translation` (
              `navigation_id` BINARY(16) NOT NULL,
              `navigation_version_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `slot_config` JSON,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3),
              PRIMARY KEY (`navigation_id`, `navigation_version_id`, `language_id`),
              CONSTRAINT `json.navigation_translation.slot_config` CHECK (JSON_VALID(`slot_config`)),
              CONSTRAINT `fk.navigation_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.navigation_translation.navigation_id` FOREIGN KEY (`navigation_id`, `navigation_version_id`)
                REFERENCES `navigation` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
