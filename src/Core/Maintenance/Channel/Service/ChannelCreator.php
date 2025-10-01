<?php declare(strict_types=1);

namespace HeyFrame\Core\Maintenance\Channel\Service;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Maintenance\MaintenanceException;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Country\CountryCollection;

/**
 * @internal
 */
#[Package('discovery')]
class ChannelCreator
{
    /**
     * @param EntityRepository<ChannelCollection> $channelRepository
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     * @param EntityRepository<CountryCollection> $countryRepository
     * @param EntityRepository<CategoryCollection> $navigationRepository
     *
     * @internal
     */
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionRegistry,
        private readonly EntityRepository $channelRepository,
        private readonly EntityRepository $paymentMethodRepository,
        private readonly EntityRepository $countryRepository,
        private readonly EntityRepository $navigationRepository
    ) {
    }

    /**
     * @param list<string>|null $currencies
     * @param list<string>|null $languages
     * @param list<string>|null $paymentMethods
     * @param list<string>|null $countries
     * @param array<string, mixed> $overwrites
     */
    public function createChannel(
        string $id,
        string $name,
        string $typeId,
        ?string $languageId = null,
        ?string $currencyId = null,
        ?string $paymentMethodId = null,
        ?string $countryId = null,
        ?string $customerGroupId = null,
        ?string $navigationCategoryId = null,
        ?array $currencies = null,
        ?array $languages = null,
        ?array $paymentMethods = null,
        ?array $countries = null,
        array $overwrites = []
    ): string {
        $context = Context::createDefaultContext();

        $languageId ??= Defaults::LANGUAGE_SYSTEM;
        $currencyId ??= Defaults::CURRENCY;
        $paymentMethodId ??= $this->getFirstActivePaymentMethodId($context);
        $countryId ??= $this->getFirstActiveCountryId($context);

        $currencies = $this->formatToMany($currencies, $currencyId, 'currency', $context);
        $languages = $this->formatToMany($languages, $languageId, 'language', $context);
        $paymentMethods = $this->formatToMany($paymentMethods, $paymentMethodId, 'payment_method', $context);
        $countries = $this->formatToMany($countries, $countryId, 'country', $context);

        $data = [
            'id' => $id,
            'name' => $name,
            'typeId' => $typeId,
            'accessKey' => AccessKeyHelper::generateAccessKey('channel'),

            // default selection
            'languageId' => $languageId,
            'currencyId' => $currencyId,
            'paymentMethodId' => $paymentMethodId,
            'countryId' => $countryId,
            'customerGroupId' => $customerGroupId ?? $this->getCustomerGroupId($context),
            'navigationId' => $navigationCategoryId ?? $this->getRootNavigationId($context),

            // available mappings
            'currencies' => $currencies,
            'languages' => $languages,
            'paymentMethods' => $paymentMethods,
            'countries' => $countries,
        ];

        $data = array_replace_recursive($data, $overwrites);

        $this->channelRepository->create([$data], $context);

        return $data['accessKey'];
    }

    private function getFirstActivePaymentMethodId(Context $context): string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('position'));

        $paymentMethodId = $this->paymentMethodRepository->searchIds($criteria, $context)->firstId();
        if (!\is_string($paymentMethodId)) {
            throw MaintenanceException::couldNotGetId('first active payment method');
        }

        return $paymentMethodId;
    }

    private function getFirstActiveCountryId(Context $context): string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('position'));

        $countryId = $this->countryRepository->searchIds($criteria, $context)->firstId();
        if (!\is_string($countryId)) {
            throw MaintenanceException::couldNotGetId('first active country');
        }

        return $countryId;
    }

    private function getRootNavigationId(Context $context): string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('navigation.parentId', null));
        $criteria->addSorting(new FieldSorting('navigation.createdAt', FieldSorting::ASCENDING));

        $navigationId = $this->navigationRepository->searchIds($criteria, $context)->firstId();
        if (!\is_string($navigationId)) {
            throw MaintenanceException::couldNotGetId('root navigation');
        }

        return $navigationId;
    }

    /**
     * @return array<array{id: string}>
     */
    private function getAllIdsOf(string $entity, Context $context): array
    {
        /** @var list<string> $ids */
        $ids = $this->definitionRegistry->getRepository($entity)->searchIds(new Criteria(), $context)->getIds();

        return array_map(
            static fn (string $id): array => ['id' => $id],
            $ids
        );
    }

    private function getCustomerGroupId(Context $context): string
    {
        $criteria = (new Criteria())
            ->setLimit(1);

        $repository = $this->definitionRegistry->getRepository(CustomerGroupDefinition::ENTITY_NAME);

        $id = $repository->searchIds($criteria, $context)->firstId();

        if ($id === null) {
            throw MaintenanceException::couldNotGetId('customer group');
        }

        return $id;
    }

    /**
     * @param list<string>|null $values
     *
     * @return array<array{id: string}>
     */
    private function formatToMany(?array $values, string $default, string $entity, Context $context): array
    {
        if (!\is_array($values)) {
            return $this->getAllIdsOf($entity, $context);
        }

        $values = array_unique(array_merge($values, [$default]));

        return array_map(
            static fn (string $id): array => ['id' => $id],
            $values
        );
    }
}
