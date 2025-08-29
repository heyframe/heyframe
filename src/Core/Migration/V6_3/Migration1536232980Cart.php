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
class Migration1536232980Cart extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232980;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `cart` (
              `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `rule_ids` json NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `auto_increment` bigint NOT NULL AUTO_INCREMENT,
              `compressed` tinyint(1) NOT NULL DEFAULT \'0\',
              `payload` longblob,
              PRIMARY KEY (`token`),
              UNIQUE KEY `auto_increment` (`auto_increment`),
              KEY `idx.cart.created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
