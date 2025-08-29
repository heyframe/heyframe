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
class Migration1536232940Channel extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232940;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `channel` (
              `id` BINARY(16) NOT NULL,
              `type_id` BINARY(16) NOT NULL,
              `short_name` VARCHAR(45) NULL,
              `configuration` JSON NULL,
              `access_key` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              `currency_id` BINARY(16) NOT NULL,
              `payment_method_id` BINARY(16) NOT NULL,
              `country_id` BINARY(16) NOT NULL,
              `service_category_id` BINARY(16) NULL,
              `service_category_version_id` BINARY(16) NULL,
              `active` TINYINT(1) NOT NULL DEFAULT '1',
              `navigation_id` BINARY(16) NULL,
              `navigation_version_id` BINARY(16),
              `customer_group_id` BINARY(16) NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              UNIQUE `uniq.access_key` (`access_key`),
              CONSTRAINT `json.channel.configuration` CHECK (JSON_VALID(`configuration`)),
              CONSTRAINT `fk.channel.country_id` FOREIGN KEY (`country_id`)
                REFERENCES `country` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.channel.currency_id` FOREIGN KEY (`currency_id`)
                REFERENCES `currency` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.channel.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.channel.payment_method_id` FOREIGN KEY (`payment_method_id`)
                REFERENCES `payment_method` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.channel.type_id` FOREIGN KEY (`type_id`)
                REFERENCES `channel_type` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.channel.navigation_id` FOREIGN KEY (`navigation_id`, `navigation_version_id`)
                REFERENCES `navigation` (`id`, `version_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
              CONSTRAINT `fk.channel.customer_group_id` FOREIGN KEY (`customer_group_id`)
                REFERENCES `customer_group` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);

        $connection->executeStatement('
            CREATE TABLE `channel_translation` (
              `channel_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`channel_id`, `language_id`),
              CONSTRAINT `json.channel_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.channel_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_translation.channel_id` FOREIGN KEY (`channel_id`)
                REFERENCES `channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `channel_language` (
              `channel_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`channel_id`, `language_id`),
              CONSTRAINT `fk.channel_language.channel_id` FOREIGN KEY (`channel_id`)
                REFERENCES `channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_language.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `channel_currency` (
              `channel_id` BINARY(16) NOT NULL,
              `currency_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`channel_id`, `currency_id`),
              CONSTRAINT `fk.channel_currency.channel_id` FOREIGN KEY (`channel_id`)
                REFERENCES `channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_currency.currency_id` FOREIGN KEY (`currency_id`)
                REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `channel_country` (
              `channel_id` BINARY(16) NOT NULL,
              `country_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`channel_id`, `country_id`),
              CONSTRAINT `fk.channel_country.channel_id` FOREIGN KEY (`channel_id`)
                REFERENCES `channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_country.country_id` FOREIGN KEY (`country_id`)
                REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `channel_payment_method` (
              `channel_id` BINARY(16) NOT NULL,
              `payment_method_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`channel_id`, `payment_method_id`),
              CONSTRAINT `fk.channel_payment_method.channel_id` FOREIGN KEY (`channel_id`)
                REFERENCES `channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_payment_method.payment_method_id` FOREIGN KEY (`payment_method_id`)
                REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
