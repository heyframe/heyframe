<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1565079228AddAclStructure extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1565079228;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `acl_role` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `privileges` json NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `deleted_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `acl_user_role` (
              `user_id` binary(16) NOT NULL,
              `acl_role_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`user_id`,`acl_role_id`),
              KEY `fk.acl_user_role.acl_role_id` (`acl_role_id`),
              CONSTRAINT `fk.acl_user_role.acl_role_id` FOREIGN KEY (`acl_role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.acl_user_role.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        $connection->executeStatement('ALTER TABLE `user` ADD `admin` tinyint(1) NULL AFTER `active`');

        $connection->executeStatement('UPDATE `user` SET `admin` = 1');

        $connection->executeStatement('
            CREATE TABLE `customer_role` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `config` json DEFAULT NULL,
              `extra_fields` json DEFAULT NULL,
              `active` tinyint(1) NOT NULL DEFAULT \'1\',
              `privileges` json NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `deleted_at` datetime(3) DEFAULT NULL,
              UNIQUE KEY `uniq.name` (`name`),
              PRIMARY KEY (`id`),
              CONSTRAINT `json.customer_role.config` CHECK (json_valid(`config`)),
              CONSTRAINT `json.customer_role.extra_fields` CHECK (json_valid(`extra_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `customer_role_mapping` (
              `customer_id` binary(16) NOT NULL,
              `customer_role_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`customer_id`,`customer_role_id`),
              KEY `fk.customer_role_mapping.customer_role_id` (`customer_role_id`),
              CONSTRAINT `fk.customer_role_mapping.customer_role_id` FOREIGN KEY (`customer_role_id`) REFERENCES `customer_role` (`id`) ON DELETE RESTRICT,
              CONSTRAINT `fk.customer_role_mapping.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $config = json_encode([
            'label' => [
                'zh-CN' => '默认角色',
                'en-GB' => 'Default role',
            ],
        ]);

        $privileges = json_encode([
            'customer.password.change',
        ]);

        $connection->insert('customer_role', ['id' => Uuid::randomBytes(), 'name' => 'default', 'active' => 1, 'config' => $config, 'privileges' => $privileges, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
