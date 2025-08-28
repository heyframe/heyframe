<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Database;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Kernel;

/**
 * @internal
 */
#[Package('framework')]
class ReplicaConnection
{
    public static function ensurePrimary(): void
    {
        $connection = Kernel::getConnection();

        if ($connection instanceof PrimaryReadReplicaConnection) {
            $connection->ensureConnectedToPrimary();
        }
    }
}
