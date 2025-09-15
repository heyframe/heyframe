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
class Migration1536233240ProductStreamFilter extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233240;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_stream_filter` (
              `id` binary(16) NOT NULL,
              `product_stream_id` binary(16) NOT NULL,
              `parent_id` binary(16) DEFAULT NULL,
              `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `field` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `operator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `value` longtext COLLATE utf8mb4_unicode_ci,
              `parameters` longtext COLLATE utf8mb4_unicode_ci,
              `position` int NOT NULL DEFAULT \'0\',
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.product_stream_filter.product_stream_id` (`product_stream_id`),
              KEY `fk.product_stream_filter.parent_id` (`parent_id`),
              CONSTRAINT `fk.product_stream_filter.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `product_stream_filter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_stream_filter.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.product_stream_filter.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.product_stream_filter.parameters` CHECK (json_valid(`parameters`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
