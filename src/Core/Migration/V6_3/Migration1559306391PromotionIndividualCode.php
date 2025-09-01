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
class Migration1559306391PromotionIndividualCode extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1559306391;
    }

    public function update(Connection $connection): void
    {
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
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
