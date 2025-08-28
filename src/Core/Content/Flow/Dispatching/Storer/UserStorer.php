<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\UserAware;
use HeyFrame\Core\System\User\Aggregate\UserRecovery\UserRecoveryCollection;
use HeyFrame\Core\System\User\Aggregate\UserRecovery\UserRecoveryDefinition;
use HeyFrame\Core\System\User\Aggregate\UserRecovery\UserRecoveryEntity;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserStorer extends FlowStorer
{
    /**
     * @internal
     *
     * @param EntityRepository<UserRecoveryCollection> $userRecoveryRepository
     */
    public function __construct(
        private readonly EntityRepository $userRecoveryRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function store(FlowEventAware $event, array $stored): array
    {
        if (!$event instanceof UserAware || isset($stored[UserAware::USER_RECOVERY_ID])) {
            return $stored;
        }

        $stored[UserAware::USER_RECOVERY_ID] = $event->getUserId();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(UserAware::USER_RECOVERY_ID)) {
            return;
        }

        $storable->lazy(
            UserAware::USER_RECOVERY,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?UserRecoveryEntity
    {
        $id = $storableFlow->getStore(UserAware::USER_RECOVERY_ID);
        if ($id === null) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadUserRecovery($criteria, $storableFlow->getContext(), $id);
    }

    private function loadUserRecovery(Criteria $criteria, Context $context, string $id): ?UserRecoveryEntity
    {
        $criteria->addAssociation('user');

        $event = new BeforeLoadStorableFlowDataEvent(
            UserRecoveryDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        $user = $this->userRecoveryRepository->search($criteria, $context)->getEntities()->get($id);

        if ($user) {
            return $user;
        }

        return null;
    }
}
