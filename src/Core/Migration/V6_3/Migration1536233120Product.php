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
class Migration1536233120Product extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233120;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `auto_increment` BIGINT unsigned NOT NULL AUTO_INCREMENT,
              `product_number` VARCHAR(64) NULL,
              `product_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `active` TINYINT(1) unsigned NOT NULL DEFAULT 1,
              `parent_id` BINARY(16) NULL,
              `parent_version_id` BINARY(16) NULL,
              `product_media_id` BINARY(16) NULL,
              `product_media_version_id` BINARY(16) NULL,
              `option_ids` JSON NULL,
              `property_ids` JSON NULL,
              `cover` BINARY(16) NULL,
              `media` BINARY(16) NULL,
              `prices` BINARY(16) NULL,
              `visibilities` BINARY(16) NULL,
              `properties` BINARY(16) NULL,
              `translations` BINARY(16) NULL,
              `price` JSON NULL,
              `listing_prices` JSON NULL,
              `stock` INT(11) NULL,
              `restock_time` INT(11) NULL,
              `is_closeout` TINYINT(1) NULL,
              `purchase_steps` INT(11) unsigned NULL,
              `max_purchase` INT(11) unsigned NULL,
              `min_purchase` INT(11) unsigned NULL,
              `purchase_unit` DECIMAL(11,4) unsigned NULL,
              `reference_unit` DECIMAL(10,3) unsigned NULL,
              `purchase_price` DOUBLE NULL,
              `release_date` DATETIME(3) NULL,
              `variant_restrictions` JSON NULL,
              `configurator_group_sorting` JSON NULL,
              `created_at` DATETIME(3) NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`, `version_id`),
              KEY `idx.auto_increment` (`auto_increment`),
              CONSTRAINT `uniq.product.product_number__version_id` UNIQUE (`product_number`, `version_id`),
              CONSTRAINT `json.product.option_ids` CHECK (JSON_VALID(`option_ids`)),
              CONSTRAINT `json.product.property_ids` CHECK (JSON_VALID(`property_ids`)),
              CONSTRAINT `json.product.price` CHECK (JSON_VALID(`price`)),
              CONSTRAINT `json.product.listing_prices` CHECK (JSON_VALID(`listing_prices`)),
              CONSTRAINT `json.product.variant_restrictions` CHECK (JSON_VALID(`variant_restrictions`)),
              CONSTRAINT `json.product.configurator_group_sorting` CHECK (JSON_VALID(`configurator_group_sorting`)),
              CONSTRAINT `fk.product.parent_id` FOREIGN KEY (`parent_id`, `parent_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `product_translation` (
              `product_id` BINARY(16) NOT NULL,
              `product_version_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              `additional_text` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `keywords` MEDIUMTEXT COLLATE utf8mb4_unicode_ci NULL,
              `description` MEDIUMTEXT COLLATE utf8mb4_unicode_ci NULL,
              `meta_title` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`product_id`, `product_version_id`, `language_id`),
              CONSTRAINT `json.product_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.product_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_translation.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
