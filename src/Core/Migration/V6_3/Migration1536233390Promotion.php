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
class Migration1536233390Promotion extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233390;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `promotion` (
              `id` binary(16) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              `valid_from` datetime DEFAULT NULL,
              `valid_until` datetime DEFAULT NULL,
              `max_redemptions_global` int DEFAULT NULL,
              `max_redemptions_per_customer` int DEFAULT NULL,
              `priority` int NOT NULL DEFAULT 1,
              `order_count` int NOT NULL DEFAULT 0,
              `orders_per_customer_count` json DEFAULT NULL,
              `exclusive` tinyint(1) NOT NULL DEFAULT 0,
              `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `use_codes` tinyint(1) NOT NULL DEFAULT 0,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `customer_restriction` tinyint(1) NOT NULL DEFAULT 0,
              `prevent_combination` tinyint(1) NOT NULL DEFAULT 0,
              `exclusion_ids` json DEFAULT NULL,
              `use_individual_codes` tinyint(1) NOT NULL DEFAULT 0,
              `individual_code_pattern` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `use_setgroups` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`),
              UNIQUE KEY `individual_code_pattern` (`individual_code_pattern`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_translation` (
              `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `promotion_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`promotion_id`,`language_id`),
              KEY `fk.promotion_translation.promotion_id` (`promotion_id`),
              KEY `fk.promotion_translation.language_id` (`language_id`),
              CONSTRAINT `fk.promotion_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.promotion_translation.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.promotion_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
