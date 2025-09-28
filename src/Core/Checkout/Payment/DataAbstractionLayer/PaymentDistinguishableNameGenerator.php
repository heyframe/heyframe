<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\DataAbstractionLayer;

use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\PluginEntity;

#[Package('checkout')]
class PaymentDistinguishableNameGenerator
{
    /**
     * @internal
     *
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     */
    public function __construct(private readonly EntityRepository $paymentMethodRepository)
    {
    }

    public function generateDistinguishablePaymentNames(Context $context): void
    {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $payments = $this->getInstalledPayments($context);

            $upsertablePayments = $this->generateDistinguishableNamesPayload($payments);
            if (\count($upsertablePayments) === 0) {
                return;
            }

            $this->paymentMethodRepository->upsert($upsertablePayments, $context);
        });
    }

    private function getInstalledPayments(Context $context): PaymentMethodCollection
    {
        $criteria = new Criteria();
        $criteria
            ->addAssociation('translations')
            ->addAssociation('plugin.translations');

        return $this->paymentMethodRepository->search($criteria, $context)->getEntities();
    }

    /**
     * @return array<array{id: string, distinguishableName: array<string, string>}>
     */
    private function generateDistinguishableNamesPayload(PaymentMethodCollection $payments): array
    {
        $upsertablePayments = [];
        foreach ($payments as $payment) {
            $pluginOrAppEntity = $payment->getPlugin();
            if ($pluginOrAppEntity === null || $payment->getTranslations() === null) {
                continue;
            }

            $distinguishableNames = [];
            foreach ($payment->getTranslations() as $translation) {
                $languageId = $translation->getLanguageId();

                $distinguishableNames[$languageId] = $this->generatePaymentName(
                    $pluginOrAppEntity,
                    $languageId,
                    $translation->getName() ?? $payment->getTranslation('name'),
                );
            }

            $distinguishableNames = array_filter($distinguishableNames);
            if (\count($distinguishableNames) === 0) {
                continue;
            }

            $upsertablePayments[] = [
                'id' => $payment->getId(),
                'distinguishableName' => $distinguishableNames,
            ];
        }

        return $upsertablePayments;
    }

    private function generatePaymentName(
        AppEntity|PluginEntity $entity,
        string $languageId,
        string $paymentName,
    ): ?string {
        $label = $entity->getTranslations()?->filterByProperty('languageId', $languageId)->first()?->getLabel()
            ?? $entity->getTranslation('label');

        if (!\is_string($label)) {
            return null;
        }

        return \sprintf(
            '%s | %s',
            $paymentName,
            $label
        );
    }
}
