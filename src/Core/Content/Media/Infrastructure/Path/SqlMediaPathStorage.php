<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Infrastructure\Path;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Media\Core\Application\MediaPathStorage;
use HeyFrame\Core\Framework\DataAbstractionLayer\Util\StatementHelper;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @codeCoverageIgnore
 *
 * @see \HeyFrame\Tests\Integration\Core\Content\Media\Infrastructure\Path\MediaPathStorageTest
 */
class SqlMediaPathStorage implements MediaPathStorage
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @param array<string, string> $paths
     */
    public function media(array $paths): void
    {
        $update = $this->connection->prepare('UPDATE media SET path = :path WHERE id = :id');

        foreach ($paths as $id => $path) {
            StatementHelper::executeStatement($update, ['path' => $path, 'id' => Uuid::fromHexToBytes($id)]);
        }
    }

    /**
     * @param array<string, string> $paths
     */
    public function thumbnails(array $paths): void
    {
        $update = $this->connection->prepare('UPDATE media_thumbnail SET path = :path WHERE id = :id');

        foreach ($paths as $id => $path) {
            StatementHelper::executeStatement($update, [':path' => $path, ':id' => Uuid::fromHexToBytes($id)]);
        }
    }
}
