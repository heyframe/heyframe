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
class Migration1536233090MediaThumbnail extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233090;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `media_thumbnail` (
              `id` binary(16) NOT NULL,
              `media_id` binary(16) NOT NULL,
              `width` int unsigned NOT NULL,
              `height` int unsigned NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `media_thumbnail_size_id` binary(16) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.media_thumbnail.media_id` (`media_id`),
              KEY `fk.media_thumbnail.media_thumbnail_size_id` (`media_thumbnail_size_id`),
              CONSTRAINT `fk.media_thumbnail.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.media_thumbnail.media_thumbnail_size_id` FOREIGN KEY (`media_thumbnail_size_id`) REFERENCES `media_thumbnail_size` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.media_thumbnail.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
