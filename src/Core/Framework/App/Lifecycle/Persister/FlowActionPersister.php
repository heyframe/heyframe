<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Persister;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\App\Aggregate\FlowAction\AppFlowActionCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Flow\Action\Action;
use HeyFrame\Core\Framework\App\Source\SourceResolver;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('framework')]
class FlowActionPersister
{
    /**
     * @param EntityRepository<AppFlowActionCollection> $flowActionsRepository
     */
    public function __construct(
        private readonly EntityRepository $flowActionsRepository,
        private readonly SourceResolver $sourceResolver,
        private readonly Connection $connection
    ) {
    }

    public function updateActions(AppEntity $app, Action $flowAction, Context $context, string $defaultLocale): void
    {
        $existingFlowActions = $this->connection->fetchAllKeyValue('SELECT name, LOWER(HEX(id)) FROM app_flow_action WHERE app_id = :appId', [
            'appId' => Uuid::fromHexToBytes($app->getId()),
        ]);

        $flowActions = $flowAction->getActions() ? $flowAction->getActions()->getActions() : [];
        $fs = $this->sourceResolver->filesystemForApp($app);
        $upserts = [];

        foreach ($flowActions as $action) {
            $icon = $action->getMeta()->getIcon();
            if ($icon && $fs->has('Resources', $icon)) {
                $icon = $fs->read('Resources', $icon);
            }

            $payload = array_merge([
                'appId' => $app->getId(),
                'iconRaw' => $icon,
            ], $action->toArray($defaultLocale));

            $existing = $existingFlowActions[$action->getMeta()->getName()] ?? null;
            if ($existing) {
                $payload['id'] = $existing;
                unset($existingFlowActions[$action->getMeta()->getName()]);
            }

            $upserts[] = $payload;
        }

        if (!empty($upserts)) {
            $this->flowActionsRepository->upsert($upserts, $context);
        }

        $this->deleteOldAppFlowActions(\array_values($existingFlowActions), $context);
    }

    /**
     * @param string[] $ids
     */
    private function deleteOldAppFlowActions(array $ids, Context $context): void
    {
        if (empty($ids)) {
            return;
        }

        $ids = array_map(static fn (string $id): array => ['id' => $id], $ids);

        $this->flowActionsRepository->delete($ids, $context);
    }
}
