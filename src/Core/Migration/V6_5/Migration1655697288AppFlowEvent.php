<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_5;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1655697288AppFlowEvent extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1655697288;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `app_flow_event` (
              `id` binary(16) NOT NULL,
              `app_id` binary(16) NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `aware` json NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`),
              UNIQUE KEY `uniq.app_flow_event.name` (`name`),
              KEY `fk.app_flow_event.app_id` (`app_id`),
              CONSTRAINT `fk.app_flow_event.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.app_flow_event.aware` CHECK (json_valid(`aware`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $created = $this->addColumn(
            connection: $connection,
            table: 'flow',
            column: 'app_flow_event_id',
            type: 'BINARY(16)'
        );

        if ($created) {
            $connection->executeStatement(
                'ALTER TABLE `flow`
                ADD CONSTRAINT `fk.flow.app_flow_event_id` FOREIGN KEY (`app_flow_event_id`) REFERENCES `app_flow_event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;'
            );
        }
    }
}
