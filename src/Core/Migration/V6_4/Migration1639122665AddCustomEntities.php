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
class Migration1639122665AddCustomEntities extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1639122665;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
         CREATE TABLE `custom_entity` (
          `id` binary(16) NOT NULL,
          `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
          `fields` json NOT NULL,
          `app_id` binary(16) DEFAULT NULL,
          `created_at` datetime(3) NOT NULL,
          `updated_at` datetime(3) DEFAULT NULL,
          `flags` json DEFAULT NULL,
          `plugin_id` binary(16) DEFAULT NULL,
          `custom_fields_aware` tinyint(1) NOT NULL DEFAULT \'0\',
          `label_property` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `deleted_at` datetime(3) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `name` (`name`),
          KEY `app_id` (`app_id`),
          KEY `fk.custom_entity.plugin_id` (`plugin_id`),
          CONSTRAINT `fk.custom_entity.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk.custom_entity.plugin_id` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `json.custom_entity.fields` CHECK (json_valid(`fields`))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
