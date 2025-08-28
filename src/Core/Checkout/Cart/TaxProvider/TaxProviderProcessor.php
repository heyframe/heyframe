<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\TaxProvider;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Exception\TaxProviderExceptions;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\TaxProvider\Struct\TaxProviderResult;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\TaxProvider\Payload\TaxProviderPayload;
use HeyFrame\Core\Framework\App\TaxProvider\Payload\TaxProviderPayloadService;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\TaxProvider\TaxProviderCollection;
use HeyFrame\Core\System\TaxProvider\TaxProviderEntity;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Package('checkout')]
class TaxProviderProcessor
{
    /**
     * @internal
     *
     * @param EntityRepository<TaxProviderCollection> $taxProviderRepository
     */
    public function __construct(
        private readonly EntityRepository $taxProviderRepository,
        private readonly LoggerInterface $logger,
        private readonly TaxAdjustment $adjustment,
        private readonly TaxProviderRegistry $registry,
        private readonly TaxProviderPayloadService $payloadService
    ) {
    }

    public function process(Cart $cart, ChannelContext $context): void
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_FREE) {
            return;
        }

        $taxProviders = $this->getTaxProviders($context);

        if ($taxProviders->count() === 0) {
            return;
        }

        $exceptions = new TaxProviderExceptions();

        $result = $this->buildTaxes(
            $taxProviders,
            $cart,
            $context,
            $exceptions
        );

        if ($exceptions->hasExceptions()) {
            $this->logger->error($exceptions->getMessage(), ['error' => $exceptions]);

            throw $exceptions;
        }

        if (!$result) {
            return;
        }

        $this->adjustment->adjust($cart, $result, $context);
    }

    private function getTaxProviders(ChannelContext $context): TaxProviderCollection
    {
        $criteria = (new Criteria())
            ->addAssociations(['availabilityRule', 'app'])
            ->addFilter(
                new AndFilter([
                    new EqualsFilter('active', true),
                    new OrFilter([
                        new EqualsFilter('availabilityRuleId', null),
                        new EqualsAnyFilter('availabilityRuleId', $context->getRuleIds()),
                    ]),
                ])
            );

        $providers = $this->taxProviderRepository->search($criteria, $context->getContext())->getEntities();

        // we can safely sort the providers in php, as we do not expect more than a couple of providers
        // otherwise we would need to sort them in the database with an index many fields to be performant
        $providers->sortByPriority();

        return $providers;
    }

    private function buildTaxes(
        TaxProviderCollection $providers,
        Cart $cart,
        ChannelContext $context,
        TaxProviderExceptions $exceptions,
    ): ?TaxProviderResult {
        /** @var TaxProviderEntity $providerEntity */
        foreach ($providers->getElements() as $providerEntity) {
            // app providers
            if ($providerEntity->getApp() && $providerEntity->getProcessUrl()) {
                return $this->handleAppRequest($providerEntity->getApp(), $providerEntity->getProcessUrl(), $cart, $context);
            }

            $provider = $this->registry->get($providerEntity->getIdentifier());

            if (!$provider) {
                $exceptions->add(
                    $providerEntity->getIdentifier(),
                    new NotFoundHttpException(\sprintf('No tax provider found for identifier %s', $providerEntity->getIdentifier()))
                );

                continue;
            }

            try {
                $taxProviderStruct = $provider->provide($cart, $context);
            } catch (\Throwable $e) {
                $exceptions->add($providerEntity->getIdentifier(), $e);

                continue;
            }

            // taxes given - no need to continue
            if ($taxProviderStruct->declaresTaxes()) {
                return $taxProviderStruct;
            }
        }

        return null;
    }

    private function handleAppRequest(
        AppEntity $app,
        string $processUrl,
        Cart $cart,
        ChannelContext $context
    ): ?TaxProviderResult {
        return $this->payloadService->request(
            $processUrl,
            new TaxProviderPayload($cart, $context),
            $app,
            $context->getContext()
        );
    }
}
