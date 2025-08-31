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
class Migration1643892702AddCaptureRefundTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1643892702;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_transaction_capture` (
              `id` binary(16) NOT NULL,
              `order_transaction_id` binary(16) NOT NULL,
              `order_transaction_version_id` binary(16) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `external_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `amount` json NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `version_id` binary(16) NOT NULL DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_transaction_capture.order_transaction_id` (`order_transaction_id`,`order_transaction_version_id`),
              KEY `fk.order_transaction_capture.state_id` (`state_id`),
              CONSTRAINT `fk.order_transaction_capture.order_transaction_id` FOREIGN KEY (`order_transaction_id`, `order_transaction_version_id`) REFERENCES `order_transaction` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_transaction_capture.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.order_transaction_capture.amount` CHECK (json_valid(`amount`)),
              CONSTRAINT `json.order_transaction_capture.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `order_transaction_capture_refund` (
              `id` binary(16) NOT NULL,
              `capture_id` binary(16) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `amount` json NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `external_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `version_id` binary(16) NOT NULL DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425,
              `capture_version_id` binary(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_transaction_capture_refund.state_id` (`state_id`),
              KEY `fk.order_transaction_capture_refund.capture_id` (`capture_id`,`capture_version_id`),
              CONSTRAINT `fk.order_transaction_capture_refund.capture_id` FOREIGN KEY (`capture_id`, `capture_version_id`) REFERENCES `order_transaction_capture` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_transaction_capture_refund.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `json.order_transaction_capture_refund.amount` CHECK (json_valid(`amount`)),
              CONSTRAINT `json.order_transaction_capture_refund.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `order_transaction_capture_refund_position` (
              `id` binary(16) NOT NULL,
              `refund_id` binary(16) NOT NULL,
              `order_line_item_id` binary(16) NOT NULL,
              `order_line_item_version_id` binary(16) NOT NULL,
              `quantity` int NOT NULL,
              `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `external_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `amount` json NOT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `version_id` binary(16) NOT NULL DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425,
              `refund_version_id` binary(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_transaction_capture_refund_position.order_line_item_id` (`order_line_item_id`,`order_line_item_version_id`),
              KEY `fk.order_transaction_capture_refund_position.refund_id` (`refund_id`,`refund_version_id`),
              CONSTRAINT `fk.order_transaction_capture_refund_position.order_line_item_id` FOREIGN KEY (`order_line_item_id`, `order_line_item_version_id`) REFERENCES `order_line_item` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_transaction_capture_refund_position.refund_id` FOREIGN KEY (`refund_id`, `refund_version_id`) REFERENCES `order_transaction_capture_refund` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.order_transaction_capture_refund_position.amount` CHECK (json_valid(`amount`)),
              CONSTRAINT `json.order_transaction_capture_refund_position.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
