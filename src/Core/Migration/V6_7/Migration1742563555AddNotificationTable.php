<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_7;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1742563555AddNotificationTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1742563555;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `notification` (
              `id` binary(16) NOT NULL,
              `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `admin_only` tinyint(1) NOT NULL DEFAULT \'0\',
              `required_privileges` json DEFAULT NULL,
              `created_by_integration_id` binary(16) DEFAULT NULL,
              `created_by_user_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.notification.created_by_integration_id` (`created_by_integration_id`),
              KEY `fk.notification.created_by_user_id` (`created_by_user_id`),
              CONSTRAINT `fk.notification.created_by_integration_id` FOREIGN KEY (`created_by_integration_id`) REFERENCES `integration` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.notification.created_by_user_id` FOREIGN KEY (`created_by_user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
