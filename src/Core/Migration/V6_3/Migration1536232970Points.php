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
class Migration1536232970Points extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232970;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `points` (
              `id` binary(16) NOT NULL,
              `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL default \'customer\',
              `referenced_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `total_points`int NOT NULL DEFAULT 0,
              `available_points`int NOT NULL DEFAULT 0,
              `frozen_points`int NOT NULL DEFAULT 0,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `custom_fields` json DEFAULT NULL,
              `extra_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.identifier.referenced_id` (`identifier`,`referenced_id`),
              CONSTRAINT `json.points.extra_fields` CHECK (json_valid(`extra_fields`)),
              CONSTRAINT `json.points.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `points_transactions` (
              `id` binary(16) NOT NULL,
              `points_id` BINARY(16) NOT NULL,
              `tx_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `points_amount`int NOT NULL,
              `points_after`double NOT NULL,
              `referenced_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `reference_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci default null,
              `custom_fields` json DEFAULT NULL,
              `extra_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              INDEX `idx.points_id` (`points_id`),
              INDEX `idx.points_transactions.reference_type_id` (`reference_type`, `referenced_id`),
              CONSTRAINT `fk.points_transactions.points_id` FOREIGN KEY (`points_id`) REFERENCES `points` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.points_transactions.extra_fields` CHECK (json_valid(`extra_fields`)),
              CONSTRAINT `json.points_transactions.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
