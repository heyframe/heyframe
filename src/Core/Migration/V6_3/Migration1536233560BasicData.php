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
class Migration1536233560BasicData extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233560;
    }

    public function update(Connection $connection): void
    {
        // TODO: Implement update() method.
    }
}