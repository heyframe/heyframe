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
class Migration1536233210ChannelDomain extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233210;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `channel_domain` (
              `id` binary(16) NOT NULL,
              `channel_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `currency_id` binary(16) NOT NULL,
              `snippet_set_id` binary(16) NOT NULL,
              `hreflang_use_only_locale` tinyint unsigned DEFAULT \'0\',
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.channel_domain.url` (`url`),
              KEY `fk.channel_domain.currency_id` (`currency_id`),
              KEY `fk.channel_domain.snippet_set_id` (`snippet_set_id`),
              KEY `fk.channel_domain.language_id` (`language_id`),
              KEY `fk.channel_domain.channel_id` (`channel_id`),
              CONSTRAINT `fk.channel_domain.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_domain.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_domain.channel_id` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.channel_domain.snippet_set_id` FOREIGN KEY (`snippet_set_id`) REFERENCES `snippet_set` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.channel_domain.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
