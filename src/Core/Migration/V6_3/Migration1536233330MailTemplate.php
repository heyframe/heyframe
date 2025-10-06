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
class Migration1536233330MailTemplate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233330;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `mail_template_type` (
              `id` binary(16) NOT NULL,
              `technical_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `available_entities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `template_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.mail_template_type.technical_name` (`technical_name`),
              CONSTRAINT `json.mail_template_type.available_entities` CHECK (json_valid(`available_entities`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_template_type_translation` (
              `mail_template_type_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`mail_template_type_id`,`language_id`),
              KEY `fk.mail_template_type_translation.language_id` (`language_id`),
              CONSTRAINT `fk.mail_template_type_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.mail_template_type_translation.mail_template_type_id` FOREIGN KEY (`mail_template_type_id`) REFERENCES `mail_template_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.mail_template_type_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_template` (
              `id` binary(16) NOT NULL,
              `mail_template_type_id` binary(16) DEFAULT NULL,
              `system_default` tinyint unsigned NOT NULL DEFAULT \'0\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.mail_template.mail_template_type_id` (`mail_template_type_id`),
              CONSTRAINT `fk.mail_template.mail_template_type_id` FOREIGN KEY (`mail_template_type_id`) REFERENCES `mail_template_type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_template_translation` (
              `mail_template_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `sender_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `description` longtext COLLATE utf8mb4_unicode_ci,
              `content_html` longtext COLLATE utf8mb4_unicode_ci,
              `content_plain` longtext COLLATE utf8mb4_unicode_ci,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`mail_template_id`,`language_id`),
              KEY `fk.mail_template_translation.language_id` (`language_id`),
              CONSTRAINT `fk.mail_template_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.mail_template_translation.mail_template_id` FOREIGN KEY (`mail_template_id`) REFERENCES `mail_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.mail_template_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_template_media` (
              `id` binary(16) NOT NULL,
              `mail_template_id` binary(16) NOT NULL,
              `language_id` binary(16) DEFAULT NULL,
              `media_id` binary(16) NOT NULL,
              `position` int NOT NULL DEFAULT \'1\',
              PRIMARY KEY (`id`),
              KEY `fk.mail_template_media.mail_template_id` (`mail_template_id`),
              KEY `fk.mail_template_media.media_id` (`media_id`),
              KEY `fk.mail_template_media.language_id` (`language_id`),
              CONSTRAINT `fk.mail_template_media.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.mail_template_media.mail_template_id` FOREIGN KEY (`mail_template_id`) REFERENCES `mail_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.mail_template_media.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
