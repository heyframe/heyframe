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
class Migration1536232730CountryState extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232730;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `country_state` (
              `id`          BINARY(16)                              NOT NULL,
              `country_id`  BINARY(16)                              NOT NULL,
              `short_code`  VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `code`  VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `position`    INT(11)                                 NOT NULL DEFAULT 1,
              `active`      TINYINT(1)                              NOT NULL DEFAULT 1,
              `created_at`  DATETIME(3)                             NOT NULL,
              `updated_at`  DATETIME(3)                             NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.country_state.country_id` FOREIGN KEY (`country_id`)
                REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `country_state_translation` (
              `country_state_id`    BINARY(16)                              NOT NULL,
              `language_id`         BINARY(16)                              NOT NULL,
              `name`                VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `custom_fields`       JSON                                    NULL,
              `created_at`          DATETIME(3)                             NOT NULL,
              `updated_at`          DATETIME(3)                             NULL,
              PRIMARY KEY (`country_state_id`, `language_id`),
              CONSTRAINT `json.country_state_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.country_state_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.country_state_translation.country_state_id` FOREIGN KEY (`country_state_id`)
                REFERENCES `country_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `region` (
              `id`          BINARY(16)                              NOT NULL,
              `parent_id`  BINARY(16)                               NULL,
              `country_id`  BINARY(16)                              NOT NULL,
              `country_state_id`  BINARY(16)                        NOT NULL,
              `code`  VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `config` json DEFAULT NULL,
              `position`    INT(11)                                 NOT NULL DEFAULT 1,
              `active`      TINYINT(1)                              NOT NULL DEFAULT 1,
              `created_at`  DATETIME(3)                             NOT NULL,
              `updated_at`  DATETIME(3)                             NULL,
              PRIMARY KEY (`id`),
              KEY `fk.region.parent_id` (`parent_id`),
              KEY `idx.code` (`code`),
              KEY `idx.country_id` (`country_id`),
              UNIQUE KEY `uniq.country_id.country_state_id.code` (`country_id`,`country_state_id`, `code`),
              CONSTRAINT `json.region.config` CHECK (json_valid(`config`)),
              CONSTRAINT `fk.region.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `region` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.region.country_state_id` FOREIGN KEY (`country_state_id`) REFERENCES `country_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.region.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
