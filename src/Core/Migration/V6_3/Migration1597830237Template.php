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
class Migration1597830237Template extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1597830237;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `app_template` (
              `id` binary(16) NOT NULL,
              `template` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `path` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
              `active` tinyint(1) NOT NULL,
              `app_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `hash` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.template.path` (`path`(256)),
              KEY `fk.template.app_id` (`app_id`),
              CONSTRAINT `fk.template.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
