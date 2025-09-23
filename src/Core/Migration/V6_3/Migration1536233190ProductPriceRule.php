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
class Migration1536233190ProductPriceRule extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233190;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_price` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              `product_id` binary(16) NOT NULL,
              `product_version_id` binary(16) NOT NULL,
              `price` json NOT NULL,
              `quantity_start` int NOT NULL,
              `quantity_end` int DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.product_price.product_id` (`product_id`,`product_version_id`),
              KEY `fk.product_price.rule_id` (`rule_id`),
              CONSTRAINT `fk.product_price.product_id` FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_price.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.product_price.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.product_price.price` CHECK (json_valid(`price`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
