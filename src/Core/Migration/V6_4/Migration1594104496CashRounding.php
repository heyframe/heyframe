<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1594104496CashRounding extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594104496;
    }

    public function update(Connection $connection): void
    {
        $this->updateSchema($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    private function updateSchema(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `currency_country_rounding` (
              `id` binary(16) NOT NULL,
              `currency_id` binary(16) NOT NULL,
              `country_id` binary(16) NOT NULL,
              `item_rounding` json NOT NULL,
              `total_rounding` json NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) NULL,
              PRIMARY KEY (`id`),
              KEY `currency_id` (`currency_id`),
              KEY `country_id` (`country_id`),
              CONSTRAINT `currency_country_rounding_ibfk_2` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `currency_country_rounding_ibfk_3` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }
}
