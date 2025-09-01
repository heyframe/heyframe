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
class Migration1638993987AddAppFlowActionTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1638993987;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `app_flow_action` (
              `id` binary(16) NOT NULL,
              `app_id` binary(16) NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `badge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
              `delayable` tinyint(1) NOT NULL DEFAULT 0,
              `parameters` json DEFAULT NULL,
              `config` json DEFAULT NULL,
              `headers` json DEFAULT NULL,
              `requirements` json DEFAULT NULL,
              `icon` mediumblob,
              `sw_icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.app_flow_action.name` (`name`),
              KEY `fk.app_flow_action.app_id` (`app_id`),
              CONSTRAINT `fk.app_flow_action.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.app_flow_action.config` CHECK (json_valid(`config`)),
              CONSTRAINT `json.app_flow_action.headers` CHECK (json_valid(`headers`)),
              CONSTRAINT `json.app_flow_action.parameters` CHECK (json_valid(`parameters`)),
              CONSTRAINT `json.app_flow_action.requirements` CHECK (json_valid(`requirements`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `app_flow_action_translation` (
              `app_flow_action_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `description` longtext COLLATE utf8mb4_unicode_ci,
              `headline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`app_flow_action_id`,`language_id`),
              KEY `fk.app_flow_action_translation.language_id` (`language_id`),
              CONSTRAINT `fk.app_flow_action_translation.app_flow_action_id` FOREIGN KEY (`app_flow_action_id`) REFERENCES `app_flow_action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.app_flow_action_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.app_flow_action_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
