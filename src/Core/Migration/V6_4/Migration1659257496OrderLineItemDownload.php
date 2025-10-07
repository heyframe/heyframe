<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Product\State;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1659257496OrderLineItemDownload extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1659257496;
    }

    public function update(Connection $connection): void
    {
        if (!EntityDefinitionQueryHelper::columnExists($connection, 'order_line_item', 'states')) {
            $connection->executeStatement('
                ALTER TABLE `order_line_item`
                ADD COLUMN `states` JSON NULL,
                ADD CONSTRAINT `json.order_line_item.states` CHECK (JSON_VALID(`states`))
            ');
            $connection->executeStatement('
                UPDATE `order_line_item`
                SET `states` = :states
                WHERE `states` IS NULL
            ', ['states' => json_encode([State::IS_VIRTUAL])]);
        }

        $connection->executeStatement('
            CREATE TABLE `order_line_item_download` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `position` int NOT NULL DEFAULT \'1\',
              `access_granted` tinyint(1) NOT NULL DEFAULT \'0\',
              `order_line_item_id` binary(16) NOT NULL,
              `order_line_item_version_id` binary(16) NOT NULL,
              `media_id` binary(16) NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_line_item_download.media_id` (`media_id`),
              KEY `fk.order_line_item_download.order_line_item_id` (`order_line_item_id`,`order_line_item_version_id`),
              CONSTRAINT `fk.order_line_item_download.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.order_line_item_download.order_line_item_id` FOREIGN KEY (`order_line_item_id`, `order_line_item_version_id`) REFERENCES `order_line_item` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.order_line_item_download.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
