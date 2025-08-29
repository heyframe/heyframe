<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ActionButton;

use HeyFrame\Core\Framework\App\Aggregate\ActionButton\ActionButtonCollection;
use HeyFrame\Core\Framework\App\Aggregate\ActionButton\ActionButtonEntity;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Exception\InstanceIdChangeSuggestedException;
use HeyFrame\Core\Framework\App\Payload\AppPayloadServiceHelper;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppActionLoader
{
    /**
     * @param EntityRepository<ActionButtonCollection> $actionButtonRepo
     */
    public function __construct(
        private readonly EntityRepository $actionButtonRepo,
        private readonly AppPayloadServiceHelper $appPayloadServiceHelper,
    ) {
    }

    /**
     * @param array<string> $ids
     */
    public function loadAppAction(string $actionId, array $ids, Context $context): AppAction
    {
        $criteria = new Criteria([$actionId]);
        $criteria->addAssociation('app.integration');

        /** @var ActionButtonEntity $actionButton */
        $actionButton = $this->actionButtonRepo->search($criteria, $context)->getEntities()->first();

        if ($actionButton === null) {
            throw AppException::actionNotFound();
        }

        $app = $actionButton->getApp();
        \assert($app !== null);

        try {
            $source = $this->appPayloadServiceHelper->buildSource($app->getVersion(), $app->getName());
        } catch (InstanceIdChangeSuggestedException) {
            throw AppException::actionNotFound();
        }

        return new AppAction(
            $app,
            $source,
            $actionButton->getUrl(),
            $actionButton->getEntity(),
            $actionButton->getAction(),
            $ids,
            $actionId
        );
    }
}
