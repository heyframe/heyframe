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
class Migration1536232880Category extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232880;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `category` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `auto_increment` BIGINT unsigned NOT NULL AUTO_INCREMENT,
              `parent_id` BINARY(16) NULL,
              `parent_version_id` BINARY(16) NULL,
              `media_id` BINARY(16) NULL,
              `path` LONGTEXT COLLATE utf8mb4_unicode_ci,
              `after_category_id` BINARY(16),
              `after_category_version_id` BINARY(16),
              `level` INT(11) unsigned NOT NULL DEFAULT 1,
              `active` TINYINT(1) NOT NULL DEFAULT 1,
              `child_count` INT(11) unsigned NOT NULL DEFAULT 0,
              `display_nested_products` TINYINT(1) unsigned NOT NULL DEFAULT 1,
              `visible` TINYINT(1) unsigned NOT NULL DEFAULT 1,
              `type` VARCHAR(32) NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`, `version_id`),
              KEY `idx.level` (`level`),
              KEY `idx.auto_increment` (`auto_increment`),
              KEY `fk.category.media_id` (`media_id`),
              KEY `fk.category.parent_id` (`parent_id`,`parent_version_id`),
              KEY `fk.category.after_category_id` (`after_category_id`,`after_category_version_id`),
              CONSTRAINT `fk.category.media_id` FOREIGN KEY (`media_id`)
                REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.category.parent_id` FOREIGN KEY (`parent_id`, `parent_version_id`)
                REFERENCES `category` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.category.after_category_id` FOREIGN KEY (`after_category_id`, `after_category_version_id`)
                REFERENCES `category` (`id`, `version_id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `category_translation` (
              `category_id` binary(16) NOT NULL,
              `category_version_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `breadcrumb` json DEFAULT NULL,
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
              `slot_config` json DEFAULT NULL,
              PRIMARY KEY (`category_id`,`category_version_id`,`language_id`),
              KEY `fk.category_translation.language_id` (`language_id`),
              CONSTRAINT `fk.category_translation.category_id` FOREIGN KEY (`category_id`, `category_version_id`) REFERENCES `category` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.category_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.category_translation.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.category_translation.slot_config` CHECK (json_valid(`slot_config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
