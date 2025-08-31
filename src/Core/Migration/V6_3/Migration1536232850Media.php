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
class Migration1536232850Media extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232850;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `media` (
              `id` binary(16) NOT NULL,
              `user_id` binary(16) DEFAULT NULL,
              `media_folder_id` binary(16) DEFAULT NULL,
              `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `file_extension` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `file_size` int unsigned DEFAULT NULL,
              `meta_data` json DEFAULT NULL,
              `file_name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `media_type` longblob,
              `thumbnails_ro` longblob,
              `private` tinyint(1) NOT NULL DEFAULT \'0\',
              `uploaded_at` datetime(3) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `config` json DEFAULT NULL,
              `file_hash` varchar(32) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (json_unquote(json_extract(`meta_data`,_utf8mb4\'$.hash\'))) STORED,
              PRIMARY KEY (`id`),
              KEY `fk.media.user_id` (`user_id`),
              KEY `fk.media.media_folder_id` (`media_folder_id`),
              KEY `idx.media.file_extension` (`file_extension`),
              KEY `idx.media.file_name` (`file_name`(768)),
              KEY `idx.media.file_hash` (`file_hash`),
              CONSTRAINT `fk.media.media_folder_id` FOREIGN KEY (`media_folder_id`) REFERENCES `media_folder` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.media.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `json.media.meta_data` CHECK (json_valid(`meta_data`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `media_translation` (
              `media_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              `alt` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
              `title` VARCHAR(255) COLLATE  utf8mb4_unicode_ci NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`media_id`, `language_id`),
              CONSTRAINT `json.media_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.media_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.media_translation.media_id` FOREIGN KEY (`media_id`)
                REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            ALTER TABLE `user`
              ADD CONSTRAINT `fk.user.avatar_id` FOREIGN KEY (avatar_id)
                REFERENCES `media` (id) ON DELETE SET NULL;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
