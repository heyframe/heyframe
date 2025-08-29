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
class Migration1536233480ChannelApiContext extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233480;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `channel_api_context` (
              `token` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `payload` JSON NOT NULL,
              PRIMARY KEY (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
