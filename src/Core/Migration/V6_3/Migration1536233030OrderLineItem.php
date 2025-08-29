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
class Migration1536233030OrderLineItem extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233030;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_line_item` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `order_id` binary(16) NOT NULL,
              `order_version_id` binary(16) NOT NULL,
              `parent_id` binary(16) DEFAULT NULL,
              `parent_version_id` binary(16) DEFAULT NULL,
              `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `referenced_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `cover_id` binary(16) DEFAULT NULL,
              `quantity` int NOT NULL,
              `unit_price` double GENERATED ALWAYS AS (json_unquote(json_extract(`price`,_utf8mb4\'$.unitPrice\'))) VIRTUAL,
              `total_price` double GENERATED ALWAYS AS (json_unquote(json_extract(`price`,_utf8mb4\'$.totalPrice\'))) VIRTUAL,
              `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `payload` json DEFAULT NULL,
              `price_definition` json DEFAULT NULL,
              `price` json NOT NULL,
              `stackable` tinyint(1) NOT NULL DEFAULT \'1\',
              `removable` tinyint(1) NOT NULL DEFAULT \'1\',
              `good` tinyint(1) NOT NULL DEFAULT \'1\',
              `position` int NOT NULL DEFAULT \'1\',
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `states` json DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_line_item.order_id` (`order_id`,`order_version_id`),
              KEY `fk.order_line_item.parent_id` (`parent_id`,`parent_version_id`),
              KEY `fk.order_line_item.cover_id` (`cover_id`),
              CONSTRAINT `fk.order_line_item.cover_id` FOREIGN KEY (`cover_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.order_line_item.order_id` FOREIGN KEY (`order_id`, `order_version_id`) REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_line_item.parent_id` FOREIGN KEY (`parent_id`, `parent_version_id`) REFERENCES `order_line_item` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.order_line_item.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.order_line_item.payload` CHECK (json_valid(`payload`)),
              CONSTRAINT `json.order_line_item.price` CHECK (json_valid(`price`)),
              CONSTRAINT `json.order_line_item.price_definition` CHECK (json_valid(`price_definition`)),
              CONSTRAINT `json.order_line_item.states` CHECK (json_valid(`states`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
