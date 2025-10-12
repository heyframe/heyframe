<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Checkout\Wallet\WalletCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSubscriber implements EventSubscriberInterface
{
    /**
     * @param EntityRepository<WalletCollection> $walletRepository
     */
    public function __construct(
        protected readonly EntityRepository $walletRepository,
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
            }
        }
    }

    /**
     * @param list<string> $referencesIds
     */
    private function getWallets(array $referencesIds, Context $context): WalletCollection
    {
        $criteria = new Criteria();
        $orFilters = [];
        foreach ($referencesIds as $id) {
            $orFilters[] = new MultiFilter(
                MultiFilter::CONNECTION_AND,
                [
                    new EqualsFilter('customerId', $id),
                ]
            );
        }
        if (!empty($orFilters)) {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $orFilters));
        }
        /** @var WalletCollection $walletCollection */
        $walletCollection = $this->walletRepository->search($criteria, $context)->getEntities();

        return $walletCollection;
    }

    private function createWallet(string $customerId, Context $context): void
    {
        $this->walletRepository->create([[
            'customerId' => $customerId,
            'currencyId' => $context->getCurrencyId(),
        ]], $context);
    }
}
