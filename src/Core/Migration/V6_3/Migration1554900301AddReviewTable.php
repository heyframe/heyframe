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
class Migration1554900301AddReviewTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1554900301;
    }

    public function update(Connection $connection): void
    {
        // implement update

        $connection->executeStatement('
            DROP TABLE IF EXISTS `product_review`;
        ');
        $connection->executeStatement('
            CREATE TABLE `product_review` (
              `id` binary(16) NOT NULL,
              `product_id` binary(16) NOT NULL,
              `customer_id` binary(16) DEFAULT NULL,
              `channel_id` binary(16) DEFAULT NULL,
              `language_id` binary(16) DEFAULT NULL,
              `external_user` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `external_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `content` longtext COLLATE utf8mb4_unicode_ci,
              `points` double DEFAULT NULL,
              `status` tinyint(1) DEFAULT \'0\',
              `comment` longtext COLLATE utf8mb4_unicode_ci,
              `custom_fields` json DEFAULT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `product_version_id` binary(16) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.product_review.product_id` (`product_id`,`product_version_id`),
              KEY `fk.product_review.customer_id` (`customer_id`),
              KEY `fk.product_review.channel_id` (`channel_id`),
              KEY `fk.product_review.language_id` (`language_id`),
              CONSTRAINT `fk.product_review.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.product_review.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_review.product_id` FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_review.channel_id` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`),
              CONSTRAINT `json.product_review.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
