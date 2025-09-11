<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\ScheduledTask;

use HeyFrame\Core\Content\ProductStream\ProductStreamCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: UpdateProductStreamMappingTask::class)]
#[Package('inventory')]
final class UpdateProductStreamMappingTaskHandler extends ScheduledTaskHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<ScheduledTaskCollection> $repository
     * @param EntityRepository<ProductStreamCollection> $productStreamRepository
     */
    public function __construct(
        EntityRepository $repository,
        LoggerInterface $logger,
        private readonly EntityRepository $productStreamRepository
    ) {
        parent::__construct($repository, $logger);
    }

    public function run(): void
    {
        $context = Context::createCLIContext();
        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('filters.type', 'until'),
            new EqualsFilter('filters.type', 'since'),
        ]));

        /** @var array<string> $streamIds */
        $streamIds = $this->productStreamRepository->searchIds($criteria, $context)->getIds();
        $data = array_map(fn (string $id) => ['id' => $id], $streamIds);

        $this->productStreamRepository->update($data, $context);
    }
}
