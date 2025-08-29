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
class Migration1536232680Rule extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232680;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `rule` (
              `id` binary(16) NOT NULL,
              `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
              `description` longtext COLLATE utf8mb4_unicode_ci,
              `priority` int NOT NULL,
              `payload` longblob,
              `invalid` tinyint(1) NOT NULL DEFAULT \'0\',
              `areas` json DEFAULT NULL,
              `module_types` json DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.rule.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.rule.module_types` CHECK (json_valid(`module_types`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
