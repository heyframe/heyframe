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
class Migration1536232640Currency extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232640;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `currency` (
              `id` binary(16) NOT NULL,
              `iso_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
              `factor` double NOT NULL,
              `symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `position` int NOT NULL DEFAULT \'1\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `item_rounding` json DEFAULT NULL,
              `total_rounding` json DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.currency.iso_code` (`iso_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `currency_translation` (
              `currency_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `short_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`currency_id`,`language_id`),
              KEY `fk.currency_translation.language_id` (`language_id`),
              CONSTRAINT `fk.currency_translation.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.currency_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.currency_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
