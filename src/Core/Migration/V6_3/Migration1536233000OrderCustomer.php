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
class Migration1536233000OrderCustomer extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_customer` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `customer_id` BINARY(16) NULL,
              `order_id` BINARY(16) NOT NULL,
              `gender` TINYINT(1) NOT NULL DEFAULT 0,
              `order_version_id` BINARY(16) NOT NULL,
              `email` VARCHAR(254) COLLATE utf8mb4_unicode_ci NOT NULL,
              `phone_number` VARCHAR(254) COLLATE utf8mb4_unicode_ci  NULL,
              `nickname` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci  NULL,
              `customer_number` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`, `version_id`),
              CONSTRAINT `json.order_customer.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.order_customer.customer_id` FOREIGN KEY (`customer_id`)
                REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.order_customer.order_id` FOREIGN KEY (`order_id`, `order_version_id`)
                REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
