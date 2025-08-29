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
            CREATE TABLE channel_domain (
            `id` BINARY(16) NOT NULL PRIMARY KEY,
            `channel_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            `url` VARCHAR(255) NOT NULL,
            `currency_id` BINARY(16) NOT NULL,
            `snippet_set_id` BINARY(16) NOT NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            CONSTRAINT `json.channel_domain.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            CONSTRAINT `fk.channel_domain.channel_id` FOREIGN KEY (channel_id)
              REFERENCES `channel` (id) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.channel_domain.language_id` FOREIGN KEY (channel_id, language_id)
              REFERENCES `channel_language` (channel_id, language_id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk.channel_domain.currency_id` FOREIGN KEY (currency_id)
              REFERENCES `currency` (id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk.channel_domain.snippet_set_id` FOREIGN KEY (snippet_set_id)
              REFERENCES `snippet_set` (id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `uniq.channel_domain.url` UNIQUE(url)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
