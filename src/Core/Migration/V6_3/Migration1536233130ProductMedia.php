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
class Migration1536233130ProductMedia extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233130;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_media` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `position` int NOT NULL DEFAULT \'1\',
              `product_id` binary(16) NOT NULL,
              `product_version_id` binary(16) NOT NULL,
              `media_id` binary(16) NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.product_media.media_id` (`media_id`),
              KEY `fk.product_media.product_id` (`product_id`,`product_version_id`),
              CONSTRAINT `fk.product_media.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_media.product_id` FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.product_media.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
