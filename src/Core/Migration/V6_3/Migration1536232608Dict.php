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
class Migration1536232608Dict extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232608;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `dict` (
              `id`                  BINARY(16)                              NOT NULL,
              `key`                VARCHAR(50) COLLATE utf8mb4_unicode_ci  NOT NULL,
              `active` TINYINT(1) unsigned NOT NULL DEFAULT 1,
              `created_at`          DATETIME(3)                             NOT NULL,
              `updated_at`          DATETIME(3)                             NULL,
              PRIMARY KEY (`id`),
              UNIQUE `uniq.key` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `dict_translation` (
              `dict_id`   BINARY(16)                              NOT NULL,
              `language_id`         BINARY(16)                              NOT NULL,
              `label`                VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `description`         LONGTEXT                                NULL,
              `position` INT(11) NOT NULL DEFAULT 1,
              `custom_fields`       JSON                                    NULL,
              `created_at`          DATETIME(3)                             NOT NULL,
              `updated_at`          DATETIME(3)                             NULL,
              PRIMARY KEY (`dict_id`, `language_id`),
              CONSTRAINT `json.dict_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.dict_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.dict_translation.dict_id` FOREIGN KEY (`dict_id`)
                REFERENCES `dict` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `dict_item` (
              `id`                  BINARY(16)                              NOT NULL,
              `dict_id`             BINARY(16)                              NOT NULL,
              `parent_id`           BINARY(16)                             NULL,
              `active` TINYINT(1) unsigned NOT NULL DEFAULT 1,
              `value`                VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `level` int unsigned NOT NULL DEFAULT 1,
              `child_count` int unsigned NOT NULL DEFAULT 0,
              `created_at`          DATETIME(3)                             NOT NULL,
              `updated_at`          DATETIME(3)                             NULL,
              PRIMARY KEY (`id`),
              UNIQUE `uniq.dict_id.key` (`dict_id`,`value`),
              CONSTRAINT `fk.dict_item.parent_id` FOREIGN KEY (`parent_id`)
                REFERENCES `dict_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `dict_item_translation` (
              `dict_item_id`   BINARY(16)                              NOT NULL,
              `language_id`    BINARY(16)                         NOT NULL,
              `label`                VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `position` INT(11) NOT NULL DEFAULT 1,
              `description`         LONGTEXT                                NULL,
              `custom_fields`       JSON                                    NULL,
              `created_at`          DATETIME(3)                             NOT NULL,
              `updated_at`          DATETIME(3)                             NULL,
              PRIMARY KEY (`dict_item_id`, `language_id`),
              CONSTRAINT `json.dict_item_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.dict_item_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.dict_item_translation.dict_item_id` FOREIGN KEY (`dict_item_id`)
                REFERENCES `dict_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
