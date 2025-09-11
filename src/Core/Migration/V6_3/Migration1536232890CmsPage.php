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
class Migration1536232890CmsPage extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232890;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `cms_page` (
              `id` binary(16) NOT NULL,
              `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `entity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `preview_media_id` binary(16) DEFAULT NULL,
              `locked` tinyint(1) NOT NULL DEFAULT '0',
              `css_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `config` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.cms_page.preview_media_id` (`preview_media_id`),
              CONSTRAINT `fk.cms_page.preview_media_id` FOREIGN KEY (`preview_media_id`) REFERENCES `media` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.cms_page.config` CHECK (json_valid(`config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `cms_page_translation` (
              `cms_page_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`cms_page_id`,`language_id`),
              KEY `fk.cms_page_translation.language_id` (`language_id`),
              KEY `fk.cms_page_translation.cms_page_id` (`cms_page_id`),
              CONSTRAINT `fk.cms_page_translation.cms_page_id` FOREIGN KEY (`cms_page_id`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cms_page_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.cms_page_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
