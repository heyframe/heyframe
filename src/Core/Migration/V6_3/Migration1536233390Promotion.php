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
              `active` tinyint(1) NOT NULL DEFAULT \'0\',
              `valid_from` datetime DEFAULT NULL,
              `valid_until` datetime DEFAULT NULL,
              `max_redemptions_global` int DEFAULT NULL,
              `max_redemptions_per_customer` int DEFAULT NULL,
              `priority` int NOT NULL DEFAULT \'1\',
              `order_count` int NOT NULL DEFAULT \'0\',
              `orders_per_customer_count` json DEFAULT NULL,
              `exclusive` tinyint(1) NOT NULL DEFAULT \'0\',
              `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `use_codes` tinyint(1) NOT NULL DEFAULT \'0\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `customer_restriction` tinyint(1) NOT NULL DEFAULT \'0\',
              `prevent_combination` tinyint(1) NOT NULL DEFAULT \'0\',
              `exclusion_ids` json DEFAULT NULL,
              `use_individual_codes` tinyint(1) NOT NULL DEFAULT \'0\',
              `individual_code_pattern` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `use_setgroups` tinyint(1) NOT NULL DEFAULT \'0\',
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

        $connection->executeStatement('
            CREATE TABLE `promotion_cart_rule` (
              `promotion_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              PRIMARY KEY (`promotion_id`,`rule_id`),
              KEY `fk.promotion_cart_rule.rule_id` (`rule_id`),
              CONSTRAINT `fk.promotion_cart_rule.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.promotion_cart_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_discount` (
              `id` binary(16) NOT NULL,
              `promotion_id` binary(16) NOT NULL,
              `scope` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
              `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
              `value` double NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `consider_advanced_rules` tinyint(1) NOT NULL DEFAULT \'0\',
              `max_value` float DEFAULT NULL,
              `sorter_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `applier_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `usage_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `picker_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.promotion_discount.promotion_id` (`promotion_id`),
              CONSTRAINT `fk.promotion_discount.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_discount_prices` (
              `id` binary(16) NOT NULL,
              `discount_id` binary(16) NOT NULL,
              `currency_id` binary(16) NOT NULL,
              `price` float NOT NULL DEFAULT \'0\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.promotion_discount_prices.discount_id` (`discount_id`),
              KEY `fk.promotion_discount_prices.currency_id` (`currency_id`),
              CONSTRAINT `fk.promotion_discount_prices.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.promotion_discount_prices.discount_id` FOREIGN KEY (`discount_id`) REFERENCES `promotion_discount` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_discount_rule` (
              `discount_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              PRIMARY KEY (`discount_id`,`rule_id`),
              KEY `fk.promotion_discount_rule.rule_id` (`rule_id`),
              CONSTRAINT `fk.promotion_discount_rule.promotion_id` FOREIGN KEY (`discount_id`) REFERENCES `promotion_discount` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.promotion_discount_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_individual_code` (
              `id` binary(16) NOT NULL,
              `promotion_id` binary(16) NOT NULL,
              `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `payload` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`),
              KEY `idx.promotion_individual_code.promotion_id` (`promotion_id`),
              CONSTRAINT `fk.promotion_individual_code.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_order_rule` (
              `promotion_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              PRIMARY KEY (`promotion_id`,`rule_id`),
              KEY `fk.promotion_order_rule.rule_id` (`rule_id`),
              CONSTRAINT `fk.promotion_order_rule.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.promotion_order_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_persona_customer` (
              `promotion_id` binary(16) NOT NULL,
              `customer_id` binary(16) NOT NULL,
              PRIMARY KEY (`promotion_id`,`customer_id`),
              KEY `fk.promotion_persona_customer.customer_id` (`customer_id`),
              CONSTRAINT `fk.promotion_persona_customer.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.promotion_persona_customer.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_persona_rule` (
              `promotion_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              PRIMARY KEY (`promotion_id`,`rule_id`),
              KEY `fk.promotion_persona_rule.rule_id` (`rule_id`),
              CONSTRAINT `fk.promotion_persona_rule.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.promotion_persona_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_channel` (
              `id` binary(16) NOT NULL,
              `promotion_id` binary(16) NOT NULL,
              `channel_id` binary(16) NOT NULL,
              `priority` int NOT NULL DEFAULT \'0\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.promotion_channel.channel_id` (`channel_id`),
              KEY `idx.promotion_channel.promotion_id` (`promotion_id`),
              CONSTRAINT `fk.promotion_channel.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.promotion_channel.channel_id` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_setgroup` (
              `id` binary(16) NOT NULL,
              `promotion_id` binary(16) NOT NULL,
              `packager_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `sorter_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `value` double NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.promotion_setgroup.promotion_id` (`promotion_id`),
              CONSTRAINT `fk.promotion_setgroup.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `promotion_setgroup_rule` (
              `setgroup_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              PRIMARY KEY (`setgroup_id`,`rule_id`),
              KEY `fk.promotion_setgroup_rule.rule_id` (`rule_id`),
              CONSTRAINT `fk.promotion_setgroup_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.promotion_setgroup_rule.setgroup_id` FOREIGN KEY (`setgroup_id`) REFERENCES `promotion_setgroup` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
