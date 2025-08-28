<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cleanup;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: CleanupProductKeywordDictionaryTask::class)]
#[Package('inventory')]
final class CleanupProductKeywordDictionaryTaskHandler extends ScheduledTaskHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<ScheduledTaskCollection> $repository
     */
    public function __construct(
        EntityRepository $repository,
        LoggerInterface $logger,
        private readonly Connection $connection
    ) {
        parent::__construct($repository, $logger);
    }

    public function run(): void
    {
        do {
            $result = RetryableQuery::retryable(
                $this->connection,
                fn (): int => (int) $this->connection->executeStatement(
                    'DELETE FROM product_keyword_dictionary WHERE keyword NOT IN (SELECT DISTINCT keyword FROM product_search_keyword) LIMIT 1000',
                )
            );
        } while ($result > 0);
    }
}
