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
class Migration1536232960Customer extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232960;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
        CREATE TABLE `customer` (
              `id` BINARY(16) NOT NULL,
              `auto_increment` BIGINT unsigned NOT NULL AUTO_INCREMENT,
              `customer_group_id` BINARY(16) NOT NULL,
              `channel_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              `last_payment_method_id` BINARY(16) NULL,
              `avatar_id`       BINARY(16)                              NULL,
              `hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `customer_number` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `nickname` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `phone_number`     VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `password` VARCHAR(1024) COLLATE utf8mb4_unicode_ci NULL,
              `legacy_password` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `legacy_encoder` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `email` VARCHAR(254) COLLATE utf8mb4_unicode_ci NOT NULL,
              `active` TINYINT(1) NOT NULL DEFAULT 1,
              `first_login` DATE NULL,
              `last_login` DATETIME(3) NULL,
              `bound_channel_id` binary(16) DEFAULT NULL,
              `remote_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `birthday` DATE NULL,
              `last_order_date` DATETIME(3),
              `order_count` INT(5) NOT NULL DEFAULT 0,
              `order_total_amount` double DEFAULT '0',
              `last_updated_password_at` datetime(3) DEFAULT NULL,
              `tag_ids` json DEFAULT NULL,
              `custom_fields` JSON NULL,
              `extra_fields` JSON NULL,
              `created_by_id` binary(16) DEFAULT NULL,
              `updated_by_id` binary(16) DEFAULT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `hash` (`hash`),
              UNIQUE `uniq.auto_increment` (`auto_increment`),
              KEY `idx.email` (`email`),
              KEY `idx.nickname` (`nickname`),
              KEY `idx.firstlogin` (`first_login`),
              KEY `idx.lastlogin` (`last_login`),
              KEY `fk.customer.bound_channel_id` (`bound_channel_id`),
              KEY `fk.customer.created_by_id` (`created_by_id`),
              KEY `fk.customer.updated_by_id` (`updated_by_id`),
                CONSTRAINT `fk.customer.updated_by_id` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                  CONSTRAINT `fk.customer.created_by_id` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `json.customer.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `json.customer.extra_fields` CHECK (JSON_VALID(`extra_fields`)),
              CONSTRAINT `fk.customer.customer_group_id` FOREIGN KEY (`customer_group_id`)
                REFERENCES `customer_group` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.last_payment_method_id` FOREIGN KEY (`last_payment_method_id`)
                REFERENCES `payment_method` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.channel_id` FOREIGN KEY (`channel_id`)
                REFERENCES `channel` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.bound_channel_id` FOREIGN KEY (`bound_channel_id`) REFERENCES `channel` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.avatar_id` FOREIGN KEY (`avatar_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
