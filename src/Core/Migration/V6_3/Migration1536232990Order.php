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
class Migration1536232990Order extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232990;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `state_id` BINARY(16) NOT NULL,
              `auto_increment` BIGINT unsigned NOT NULL AUTO_INCREMENT,
              `order_number` VARCHAR(64),
              `currency_id` BINARY(16) NOT NULL,
              `currency_factor` DOUBLE NULL,
              `channel_id` BINARY(16) NOT NULL,
              `price` JSON NOT NULL,
              `order_date_time` datetime(3) NOT NULL,
              `order_date` date GENERATED ALWAYS AS (cast(`order_date_time` as date)) STORED,
              `amount_total` DOUBLE GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(`price`, "$.totalPrice"))) VIRTUAL,
              `amount_net` DOUBLE GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(`price`, "$.netPrice"))) VIRTUAL,
              `position_price` DOUBLE GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(`price`, "$.positionPrice"))) VIRTUAL,
              `deep_link_code` VARCHAR(32) NULL,
              `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `item_rounding` json DEFAULT NULL,
              `total_rounding` json DEFAULT NULL,
              `rule_ids` json DEFAULT NULL,
              `custom_fields` JSON NULL,
              `created_by_id` binary(16) DEFAULT NULL,
              `updated_by_id` binary(16) DEFAULT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
               PRIMARY KEY (`id`, `version_id`),
               KEY `fk.language_id` (`language_id`),
               KEY `fk.order.channel_id` (`channel_id`),
               KEY `fk.order.currency_id` (`currency_id`),
               KEY `idx.order_number` (`order_number`),
               KEY `fk.order.created_by_id` (`created_by_id`),
               KEY `fk.order.updated_by_id` (`updated_by_id`),
               INDEX `idx.state_index` (`state_id`),
               UNIQUE `uniq.auto_increment` (`auto_increment`),
               UNIQUE `uniq.deep_link_code` (`deep_link_code`, `version_id`),
               CONSTRAINT `fk.order.created_by_id` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
               CONSTRAINT `fk.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
               CONSTRAINT `char_length.order.deep_link_code` CHECK (CHAR_LENGTH(`deep_link_code`) = 32),
               CONSTRAINT `json.order.price` CHECK  (JSON_VALID(`price`)),
               CONSTRAINT `json.order.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
               CONSTRAINT `fk.order.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
               CONSTRAINT `fk.order.channel_id` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
