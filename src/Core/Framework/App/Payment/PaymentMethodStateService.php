<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Payment;

use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('checkout')]
class PaymentMethodStateService
{
    /**
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     */
    public function __construct(private readonly EntityRepository $paymentMethodRepository)
    {
    }

    public function activatePaymentMethods(string $appId, Context $context): void
    {
        $this->updatePaymentMethods($appId, $context, false, true);
    }

    public function deactivatePaymentMethods(string $appId, Context $context): void
    {
        $this->updatePaymentMethods($appId, $context, true, false);
    }

    private function updatePaymentMethods(string $appId, Context $context, bool $currentActiveState, bool $newActiveState): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appPaymentMethod.appId', $appId));
        $criteria->addFilter(new EqualsFilter('active', $currentActiveState));

        /** @var array<string> $templates */
        $templates = $this->paymentMethodRepository->searchIds($criteria, $context)->getIds();

        $updateSet = array_map(fn (string $id) => ['id' => $id, 'active' => $newActiveState], $templates);

        $this->paymentMethodRepository->update($updateSet, $context);
    }
}
