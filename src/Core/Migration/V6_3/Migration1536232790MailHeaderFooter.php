<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1536232790MailHeaderFooter extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232790;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `mail_header_footer` (
              `id` binary(16) NOT NULL,
              `system_default` tinyint unsigned NOT NULL DEFAULT \'0\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_header_footer_translation` (
              `mail_header_footer_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `description` longtext COLLATE utf8mb4_unicode_ci,
              `header_html` longtext COLLATE utf8mb4_unicode_ci,
              `header_plain` longtext COLLATE utf8mb4_unicode_ci,
              `footer_html` longtext COLLATE utf8mb4_unicode_ci,
              `footer_plain` longtext COLLATE utf8mb4_unicode_ci,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`mail_header_footer_id`,`language_id`),
              KEY `fk.mail_header_footer_translation.language_id` (`language_id`),
              CONSTRAINT `fk.mail_header_footer_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.mail_header_footer_translation.mail_header_footer_id` FOREIGN KEY (`mail_header_footer_id`) REFERENCES `mail_header_footer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
