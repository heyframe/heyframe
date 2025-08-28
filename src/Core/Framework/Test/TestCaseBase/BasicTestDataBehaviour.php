<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\TestCaseBase;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeCollection;
use HeyFrame\Core\Checkout\Order\OrderStates;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodCollection;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodEntity;
use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Country\CountryCollection;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Core\System\Salutation\SalutationCollection;
use HeyFrame\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetCollection;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateCollection;
use HeyFrame\Core\System\Tax\TaxCollection;
use HeyFrame\Core\Test\TestDefaults;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait BasicTestDataBehaviour
{
    public function getDeDeLanguageId(): string
    {
        /** @var EntityRepository<LanguageCollection> $repository */
        $repository = static::getContainer()->get('language.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('language.translationCode.code', 'de-DE'));

        /** @var string $languageId */
        $languageId = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $languageId;
    }

    abstract protected static function getContainer(): ContainerInterface;

    protected function getValidPaymentMethodId(?string $channelId = null): string
    {
        /** @var EntityRepository<PaymentMethodCollection> $repository */
        $repository = static::getContainer()->get('payment_method.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true));

        if ($channelId) {
            $criteria->addFilter(new EqualsFilter('channels.id', $channelId));
        }

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getInactivePaymentMethodId(?string $channelId = null): string
    {
        /** @var EntityRepository<PaymentMethodCollection> $repository */
        $repository = static::getContainer()->get('payment_method.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', false));

        if ($channelId) {
            $criteria->addFilter(new EqualsFilter('channels.id', $channelId));
        }

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getAvailablePaymentMethod(?string $channelId = null): PaymentMethodEntity
    {
        /** @var EntityRepository<PaymentMethodCollection> $repository */
        $repository = static::getContainer()->get('payment_method.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new EqualsFilter('availabilityRuleId', null));

        if ($channelId) {
            $criteria->addFilter(new EqualsFilter('channels.id', $channelId));
        }

        $paymentMethod = $repository->search($criteria, Context::createDefaultContext())->getEntities()->first();
        if ($paymentMethod === null) {
            throw new \LogicException('No available Payment method configured');
        }

        return $paymentMethod;
    }

    protected function getValidShippingMethodId(?string $channelId = null): string
    {
        /** @var EntityRepository<ShippingMethodCollection> $repository */
        $repository = static::getContainer()->get('shipping_method.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('name'));

        if ($channelId) {
            $criteria->addFilter(new EqualsFilter('channels.id', $channelId));
        }

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getAvailableShippingMethod(?string $channelId = null): ShippingMethodEntity
    {
        /** @var EntityRepository<ShippingMethodCollection> $repository */
        $repository = static::getContainer()->get('shipping_method.repository');

        $criteria = (new Criteria())
            ->addAssociation('prices')
            ->addFilter(new EqualsFilter('shipping_method.prices.calculation', 1))
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('name'));

        if ($channelId) {
            $criteria->addFilter(new EqualsFilter('channels.id', $channelId));
        }

        $shippingMethods = $repository->search($criteria, Context::createDefaultContext())->getEntities();

        foreach ($shippingMethods as $shippingMethod) {
            if ($shippingMethod->getAvailabilityRuleId() !== null) {
                return $shippingMethod;
            }
        }

        throw new \LogicException('No available ShippingMethod configured');
    }

    protected function getValidSalutationId(): string
    {
        /** @var EntityRepository<SalutationCollection> $repository */
        $repository = static::getContainer()->get('salutation.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addSorting(new FieldSorting('salutationKey'));

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getLocaleIdOfSystemLanguage(): string
    {
        /** @var EntityRepository<LanguageCollection> $repository */
        $repository = static::getContainer()->get('language.repository');

        $language = $repository->search(new Criteria([Defaults::LANGUAGE_SYSTEM]), Context::createDefaultContext())->getEntities()->first();
        \assert($language !== null);

        return $language->getLocaleId();
    }

    protected function getSnippetSetIdForLocale(string $locale): ?string
    {
        /** @var EntityRepository<SnippetSetCollection> $repository */
        $repository = static::getContainer()->get('snippet_set.repository');

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('iso', $locale))
            ->setLimit(1);

        return $repository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }

    /**
     * @param string|null $channelId (null when no saleschannel filtering)
     */
    protected function getValidCountryId(?string $channelId = TestDefaults::CHANNEL): string
    {
        /** @var EntityRepository<CountryCollection> $repository */
        $repository = static::getContainer()->get('country.repository');

        $criteria = (new Criteria())->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new EqualsFilter('shippingAvailable', true))
            ->addSorting(new FieldSorting('iso'));

        if ($channelId !== null) {
            $criteria->addFilter(new EqualsFilter('channels.id', $channelId));
        }

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getDeCountryId(): string
    {
        /** @var EntityRepository<CountryCollection> $repository */
        $repository = static::getContainer()->get('country.repository');

        $criteria = (new Criteria())->setLimit(1)
            ->addFilter(new EqualsFilter('iso', 'DE'));

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getValidCategoryId(): string
    {
        /** @var EntityRepository<CategoryCollection> $repository */
        $repository = static::getContainer()->get('category.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addSorting(new FieldSorting('level'), new FieldSorting('name'));

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getValidTaxId(): string
    {
        /** @var EntityRepository<TaxCollection> $repository */
        $repository = static::getContainer()->get('tax.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addSorting(new FieldSorting('name'));

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getValidDocumentTypeId(): string
    {
        /** @var EntityRepository<DocumentTypeCollection> $repository */
        $repository = static::getContainer()->get('document_type.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addSorting(new FieldSorting('technicalName'));

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getStateMachineState(string $stateMachine = OrderStates::STATE_MACHINE, string $state = OrderStates::STATE_OPEN): string
    {
        /** @var EntityRepository<StateMachineStateCollection> $repository */
        $repository = static::getContainer()->get('state_machine_state.repository');

        $criteria = new Criteria();
        $criteria
            ->setLimit(1)
            ->addFilter(new EqualsFilter('technicalName', $state))
            ->addFilter(new EqualsFilter('stateMachine.technicalName', $stateMachine));

        /** @var string $id */
        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        return $id;
    }

    protected function getCurrencyIdByIso(string $iso = 'EUR'): string
    {
        $connection = static::getContainer()->get(Connection::class);

        return Uuid::fromBytesToHex($connection->fetchOne('SELECT id FROM currency WHERE iso_code = :iso', ['iso' => $iso]));
    }
}
