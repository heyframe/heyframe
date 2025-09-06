<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\Flow\Indexing;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Flow\Indexing\FlowIndexerSubscriber;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\QueueTestBehaviour;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('after-sales')]
class FlowIndexerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use QueueTestBehaviour;

    public function testIndexingHappensAfterPluginLifecycle(): void
    {
        $connection = static::getContainer()->get(Connection::class);

        $connection->executeStatement('UPDATE `flow` SET `payload` = null, `invalid` = 0');

        $indexer = static::getContainer()->get(FlowIndexerSubscriber::class);
        $indexer->refreshPlugin();

        $this->runWorker();

        static::assertGreaterThan(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM flow WHERE payload IS NOT NULL'));
    }
}
