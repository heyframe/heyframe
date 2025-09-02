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
class Migration1536232965Wallet extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232965;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `wallet` (
              `id` binary(16) NOT NULL,
              `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL default \'customer\',
              `referenced_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `balance`double NOT NULL DEFAULT 0.0,
              `frozen_balance`double NOT NULL DEFAULT 0.0,
              `bonus_balance`double NOT NULL DEFAULT 0.0,
              `currency_id` BINARY(16) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `custom_fields` json DEFAULT NULL,
              `extra_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.identifier.referenced_id` (`identifier`,`referenced_id`),
              CONSTRAINT `fk.wallet.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.wallet.extra_fields` CHECK (json_valid(`extra_fields`)),
              CONSTRAINT `json.wallet.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `wallet_transactions` (
              `id` binary(16) NOT NULL,
              `wallet_id` BINARY(16) NOT NULL,
              `tx_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `amount`double NOT NULL,
              `balance_after`double NOT NULL,
              `referenced_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `reference_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci default null,
              `custom_fields` json DEFAULT NULL,
              `extra_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              INDEX `idx.wallet_id` (`wallet_id`),
              INDEX `idx.wallet_transactions.reference_type_id` (`reference_type`, `referenced_id`),
              CONSTRAINT `fk.wallet_transactions.wallet_id` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.wallet_transactions.extra_fields` CHECK (json_valid(`extra_fields`)),
              CONSTRAINT `json.wallet_transactions.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
