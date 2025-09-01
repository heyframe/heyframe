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
class Migration1536233420PromotionDiscount extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233420;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `promotion_discount` (
              `id` binary(16) NOT NULL,
              `promotion_id` binary(16) NOT NULL,
              `scope` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
              `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
              `value` double NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `consider_advanced_rules` tinyint(1) NOT NULL DEFAULT 0,
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
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
