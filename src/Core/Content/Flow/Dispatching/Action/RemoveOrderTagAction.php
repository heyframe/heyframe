<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Action;

use HeyFrame\Core\Content\Flow\Dispatching\DelayableAction;
use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Event\OrderAware;

/**
 * @internal
 */
class RemoveOrderTagAction extends FlowAction implements DelayableAction
{
    /**
     * @internal
     *
     * @param EntityRepository<EntityCollection<Entity>> $orderTagRepository
     */
    public function __construct(private readonly EntityRepository $orderTagRepository)
    {
    }

    public static function getName(): string
    {
        return 'action.remove.order.tag';
    }

    /**
     * @return array<int, string>
     */
    public function requirements(): array
    {
        return [OrderAware::class];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        if (!$flow->hasData(OrderAware::ORDER_ID)) {
            return;
        }

        $this->update($flow->getContext(), $flow->getConfig(), $flow->getData(OrderAware::ORDER_ID));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function update(Context $context, array $config, string $orderId): void
    {
        if (!\array_key_exists('tagIds', $config)) {
            return;
        }

        $tagIds = array_keys($config['tagIds']);

        if (empty($tagIds)) {
            return;
        }

        $tags = array_map(static fn ($tagId) => [
            'orderId' => $orderId,
            'tagId' => $tagId,
        ], $tagIds);

        $this->orderTagRepository->delete($tags, $context);
    }
}
