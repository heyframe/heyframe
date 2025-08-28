<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\Service\ProductReviewCountService;
use HeyFrame\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityDeleteEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('after-sales')]
class ProductReviewSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly ProductReviewCountService $productReviewCountService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'product_review.written' => 'createReview',
            EntityDeleteEvent::class => 'detectChangeset',
            'product_review.deleted' => 'onReviewDeleted',
        ];
    }

    public function detectChangeset(EntityDeleteEvent $event): void
    {
        foreach ($event->getCommands() as $command) {
            if (!$command instanceof DeleteCommand) {
                continue;
            }

            if ($command->getEntityName() !== ProductReviewDefinition::ENTITY_NAME) {
                continue;
            }

            $command->requestChangeSet();
        }
    }

    public function onReviewDeleted(EntityDeletedEvent $event): void
    {
        foreach ($event->getWriteResults() as $result) {
            if ($result->getEntityName() !== ProductReviewDefinition::ENTITY_NAME) {
                continue;
            }

            $changeset = $result->getChangeSet();
            \assert($changeset !== null);

            $id = $changeset->getBefore('customer_id');

            if (!\is_string($id)) {
                continue;
            }

            $this->productReviewCountService->updateReviewCountForCustomer($id);
        }
    }

    public function createReview(EntityWrittenEvent $reviewEvent): void
    {
        if ($reviewEvent->getEntityName() !== ProductReviewDefinition::ENTITY_NAME) {
            return;
        }

        /** @var list<string> $ids */
        $ids = $reviewEvent->getIds();

        $this->productReviewCountService->updateReviewCount($ids);
    }
}
