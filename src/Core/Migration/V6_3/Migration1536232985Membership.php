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
class Migration1536232985Membership extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232985;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `membership_plans` (
              `id` binary(16) NOT NULL,
              `technical_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `config` json DEFAULT NULL,
              `duration_days` int(11) NULL,
              `custom_fields` JSON NULL,
              `privileges` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.technical_name` (`technical_name`),
              CONSTRAINT `json.membership_plans.config` CHECK (json_valid(`config`)),
              CONSTRAINT `json.membership_plans.privileges` CHECK (json_valid(`privileges`)),
              CONSTRAINT `json.membership_plans.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `membership_levels` (
              `id` binary(16) NOT NULL,
              `technical_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `config` json DEFAULT NULL,
              `min_points` int(11) NULL,
              `max_points` int(11) NULL,
              `custom_fields` JSON NULL,
              `privileges` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.technical_name` (`technical_name`),
              CONSTRAINT `json.membership_levels.config` CHECK (json_valid(`config`)),
              CONSTRAINT `json.membership_levels.privileges` CHECK (json_valid(`privileges`)),
              CONSTRAINT `json.membership_levels.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `customer_memberships` (
              `id` binary(16) NOT NULL,
              `customer_id` BINARY(16) NOT NULL,
              `membership_plans_id` BINARY(16)  NULL,
              `extra_fields` json DEFAULT NULL,
              `custom_fields` JSON NULL,
              `start_at` DATETIME(3)  NULL,
              `end_at` DATETIME(3)  NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.customer_memberships.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.customer_memberships.membership_plans_id` FOREIGN KEY (`membership_plans_id`) REFERENCES `membership_plans` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.customer_memberships.extra_fields` CHECK (json_valid(`extra_fields`)),
              CONSTRAINT `json.customer_memberships.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `customer_memberships_levels` (
              `id` binary(16) NOT NULL,
              `customer_id` BINARY(16) NOT NULL,
              `membership_levels_id` BINARY(16)  NULL,
              `points` INT(11) UNSIGNED NOT NULL DEFAULT 0,
              `extra_fields` json DEFAULT NULL,
              `custom_fields` JSON NULL,
              `start_at` DATETIME(3)  NULL,
              `end_at` DATETIME(3)  NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.customer_memberships_levels.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.customer_memberships_levels.membership_levels_id` FOREIGN KEY (`membership_levels_id`) REFERENCES `membership_levels` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.customer_memberships_levels.extra_fields` CHECK (json_valid(`extra_fields`)),
              CONSTRAINT `json.customer_memberships_levels.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
