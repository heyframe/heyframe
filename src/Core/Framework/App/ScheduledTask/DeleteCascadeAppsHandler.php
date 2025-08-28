<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ScheduledTask;

use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Acl\Role\AclRoleCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use HeyFrame\Core\System\Integration\IntegrationCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: DeleteCascadeAppsTask::class)]
#[Package('framework')]
final class DeleteCascadeAppsHandler extends ScheduledTaskHandler
{
    private const HARD_DELETE_AFTER_DAYS = 1;

    /**
     * @internal
     *
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     * @param EntityRepository<AclRoleCollection> $aclRoleRepository
     * @param EntityRepository<IntegrationCollection> $integrationRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly EntityRepository $aclRoleRepository,
        private readonly EntityRepository $integrationRepository
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $context = Context::createCLIContext();
        $timeExpired = (new \DateTimeImmutable())->modify(\sprintf('-%d day', self::HARD_DELETE_AFTER_DAYS))->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter('deletedAt', [
            RangeFilter::LTE => $timeExpired,
        ]));

        $this->deleteIds($this->aclRoleRepository, $criteria, $context);
        $this->deleteIds($this->integrationRepository, $criteria, $context);
    }

    /**
     * @param EntityRepository<covariant EntityCollection<covariant Entity>> $repository
     */
    private function deleteIds(EntityRepository $repository, Criteria $criteria, Context $context): void
    {
        $data = $repository->searchIds($criteria, $context)->getData();

        if (empty($data)) {
            return;
        }

        $deleteIds = array_values($data);

        $repository->delete($deleteIds, $context);
    }
}
