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
#[Package('after-sales')]
class Migration1659257296InsertFlowData extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1659257296;
    }

    public function update(Connection $connection): void
    {
    }
}
