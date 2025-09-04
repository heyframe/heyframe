<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Checkout\Wallet\WalletCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Points\PointsCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerWrittenSubscriber implements EventSubscriberInterface
{
    /**
     * @param EntityRepository<WalletCollection> $walletRepository
     * @param EntityRepository<PointsCollection> $pointsRepository
     */
    public function __construct(
        private readonly EntityRepository $walletRepository,
        private readonly EntityRepository $pointsRepository,
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
                $this->createWallet($customerId, $event->getContext());
                $this->createPoints($customerId, $event->getContext());
            }
        }
    }

    private function createWallet(string $customerId, Context $context): void
    {
        $this->walletRepository->create([[
            'referencedId' => $customerId,
            'currencyId' => $context->getCurrencyId(),
            'identifier' => CustomerDefinition::ENTITY_NAME,
        ]], $context);
    }

    private function createPoints(string $customerId, Context $context): void
    {
        $this->pointsRepository->create([[
            'identifier' => CustomerDefinition::ENTITY_NAME,
            'referencedId' => $customerId,
        ]], $context);
    }
}
