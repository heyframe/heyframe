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
class Migration1601539530IntergrationRoleEntity extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1601539530;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `integration_role` (
              `integration_id` binary(16) NOT NULL,
              `acl_role_id` binary(16) NOT NULL,
              PRIMARY KEY (`integration_id`,`acl_role_id`),
              KEY `fk.integration_acl_role.acl_role_id` (`acl_role_id`),
              CONSTRAINT `fk.integration_acl_role.acl_role_id` FOREIGN KEY (`acl_role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.integration_acl_role.integration_id` FOREIGN KEY (`integration_id`) REFERENCES `integration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
