<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Points\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Checkout\Points\PointsCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('discovery')]
class CustomerSubscriber implements EventSubscriberInterface
{
    /**
     * @param EntityRepository<PointsCollection> $pointsRepository
     */
    public function __construct(
        protected readonly EntityRepository $pointsRepository,
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::CUSTOMER_WRITTEN_EVENT => 'onCustomerWritten',
        ];
    }

    public function onCustomerWritten(EntityWrittenEvent $event): void
    {
        foreach ($event->getWriteResults() as $writeResult) {
            if ($writeResult->getOperation() !== EntityWriteResult::OPERATION_INSERT) {
                continue;
            }

            $payload = $writeResult->getPayload();
            $customerId = $payload['id'] ?? null;

            if ($customerId) {
                $this->createPoints($customerId, $event->getContext());
            }
        }
    }

    private function createPoints(string $customerId, Context $context): void
    {
        $this->pointsRepository->create([[
            'customerId' => $customerId,
        ]], $context);
    }
}
