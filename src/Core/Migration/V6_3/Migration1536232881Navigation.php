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
class Migration1536232881Navigation extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232881;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `navigation` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `auto_increment` BIGINT unsigned NOT NULL AUTO_INCREMENT,
              `parent_id` BINARY(16) NULL,
              `parent_version_id` BINARY(16) NULL,
              `media_id` BINARY(16) NULL,
              `path` LONGTEXT COLLATE utf8mb4_unicode_ci,
              `after_navigation_id` BINARY(16),
              `after_navigation_version_id` BINARY(16),
              `level` INT(11) unsigned NOT NULL DEFAULT 1,
              `active` TINYINT(1) NOT NULL DEFAULT 1,
              `child_count` INT(11) unsigned NOT NULL DEFAULT 0,
              `visible` TINYINT(1) unsigned NOT NULL DEFAULT 1,
              `type` VARCHAR(32) NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`, `version_id`),
              KEY `idx.level` (`level`),
              KEY `idx.auto_increment` (`auto_increment`),
              KEY `fk.navigation.media_id` (`media_id`),
              KEY `fk.navigation.parent_id` (`parent_id`,`parent_version_id`),
              KEY `fk.navigation.after_navigation_id` (`after_navigation_id`,`after_navigation_version_id`),
              CONSTRAINT `fk.navigation.media_id` FOREIGN KEY (`media_id`)
                REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.navigation.parent_id` FOREIGN KEY (`parent_id`, `parent_version_id`)
                REFERENCES `navigation` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.navigation.after_navigation_id` FOREIGN KEY (`after_navigation_id`, `after_navigation_version_id`)
                REFERENCES `navigation` (`id`, `version_id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `navigation_translation` (
              `navigation_id` binary(16) NOT NULL,
              `navigation_version_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `internal_link` binary(16) DEFAULT NULL,
              `link_new_tab` tinyint DEFAULT NULL,
              `link_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `external_link` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`navigation_id`,`navigation_version_id`,`language_id`),
              KEY `fk.navigation_translation.language_id` (`language_id`),
              CONSTRAINT `fk.navigation_translation.navigation_id` FOREIGN KEY (`navigation_id`, `navigation_version_id`) REFERENCES `navigation` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.navigation_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.navigation_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
