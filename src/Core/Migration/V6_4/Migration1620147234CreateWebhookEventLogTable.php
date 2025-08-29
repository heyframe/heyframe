<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1620147234CreateWebhookEventLogTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1620147234;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `webhook_event_log` (
              `id` binary(16) NOT NULL,
              `app_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `webhook_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
              `event_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `delivery_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `timestamp` int DEFAULT NULL,
              `processing_time` int DEFAULT NULL,
              `app_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `request_content` json DEFAULT NULL,
              `response_content` json DEFAULT NULL,
              `response_status_code` int DEFAULT NULL,
              `response_reason_phrase` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
              `serialized_webhook_message` longblob,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `only_live_version` tinyint unsigned NOT NULL DEFAULT \'0\',
              PRIMARY KEY (`id`),
              CONSTRAINT `json.webhook_event_log.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.webhook_event_log.request_content` CHECK (json_valid(`request_content`)),
              CONSTRAINT `json.webhook_event_log.response_content` CHECK (json_valid(`response_content`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
