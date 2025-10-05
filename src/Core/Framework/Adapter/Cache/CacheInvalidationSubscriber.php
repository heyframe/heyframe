<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\CachedRuleLoader;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use HeyFrame\Core\Checkout\Payment\Channel\PaymentMethodRoute;
use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
use HeyFrame\Core\Content\Media\Event\MediaIndexerEvent;
use HeyFrame\Core\Content\Product\Aggregate\ProductProperty\ProductPropertyDefinition;
use HeyFrame\Core\Content\Product\Channel\Detail\ProductDetailRoute;
use HeyFrame\Core\Content\Product\Events\InvalidateProductCache;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOptionTranslation\PropertyGroupOptionTranslationDefinition;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupTranslation\PropertyGroupTranslationDefinition;
use HeyFrame\Core\Content\Property\PropertyGroupDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Adapter\Translation\Translator;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCountry\ChannelCountryDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCurrency\ChannelCurrencyDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelLanguage\ChannelLanguageDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelPaymentMethod\ChannelPaymentMethodDefinition;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\Channel\Context\CachedBaseChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\CachedChannelContextFactory;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;
use HeyFrame\Core\System\Country\Channel\CountryRoute;
use HeyFrame\Core\System\Country\Channel\CountryStateRoute;
use HeyFrame\Core\System\Country\CountryDefinition;
use HeyFrame\Core\System\Currency\Channel\CurrencyRoute;
use HeyFrame\Core\System\Currency\CurrencyDefinition;
use HeyFrame\Core\System\Language\Channel\LanguageRoute;
use HeyFrame\Core\System\Language\LanguageDefinition;
use HeyFrame\Core\System\Snippet\SnippetDefinition;
use HeyFrame\Core\System\StateMachine\Loader\InitialStateIdLoader;
use HeyFrame\Core\System\StateMachine\StateMachineDefinition;
use HeyFrame\Core\System\SystemConfig\CachedSystemConfigLoader;

#[Package('framework')]
/**
 * @internal
 */
class CacheInvalidationSubscriber
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
        private readonly Connection $connection,
    ) {
    }

    public function invalidateInitialStateIdLoader(EntityWrittenContainerEvent $event): void
    {
        if (!$event->getPrimaryKeys(StateMachineDefinition::ENTITY_NAME)) {
            return;
        }

        $this->cacheInvalidator->invalidate([InitialStateIdLoader::CACHE_KEY], true);
    }

    public function invalidateConfig(): void
    {
        // invalidates the complete cached config immediately
        $this->cacheInvalidator->invalidate([CachedSystemConfigLoader::CACHE_TAG], true);
    }

    public function invalidateSnippets(EntityWrittenContainerEvent $event): void
    {
        // invalidates all http cache items where the snippets used
        $snippets = $event->getEventByEntityName(SnippetDefinition::ENTITY_NAME);

        if (!$snippets) {
            return;
        }

        $setIds = $this->getSetIds($snippets->getIds());

        if (empty($setIds)) {
            return;
        }

        $this->cacheInvalidator->invalidate(array_map(Translator::tag(...), $setIds));
    }

    public function invalidateRules(): void
    {
        // immediately invalidates the rule loader each time a rule changed or a plugin install state changed
        $this->cacheInvalidator->invalidate([CachedRuleLoader::CACHE_KEY], true);
    }

    public function invalidateProduct(InvalidateProductCache $event): void
    {
        $parents = array_map(ProductDetailRoute::buildName(...), $this->getParentIds($event->getIds()));

        $tags = array_merge($parents);

        $this->cacheInvalidator->invalidate($tags, force: $event->force);
    }

    public function invalidateCurrencyRoute(EntityWrittenContainerEvent $event): void
    {
        // invalidates the currency route when a currency changed or an assignment between the sales channel and currency changed
        $this->cacheInvalidator->invalidate([
            ...$this->getChangedCurrencyAssignments($event),
            ...$this->getChangedCurrencies($event),
        ]);
    }

    public function invalidateLanguageRoute(EntityWrittenContainerEvent $event): void
    {
        // invalidates the language route when a language changed or an assignment between the sales channel and language changed
        $this->cacheInvalidator->invalidate([
            ...$this->getChangedLanguageAssignments($event),
            ...$this->getChangedLanguages($event),
        ]);
    }

    public function invalidateCountryRoute(EntityWrittenContainerEvent $event): void
    {
        // invalidates the country route when a country changed or an assignment between the sales channel and country changed
        $this->cacheInvalidator->invalidate([
            ...$this->getChangedCountryAssignments($event),
            ...$this->getChangedCountries($event),
        ]);
    }

    public function invalidateCountryStateRoute(EntityWrittenContainerEvent $event): void
    {
        $tags = [];
        if (
            $event->getDeletedPrimaryKeys(CountryStateDefinition::ENTITY_NAME)
            || $event->getPrimaryKeysWithPropertyChange(CountryStateDefinition::ENTITY_NAME, ['countryId'])
        ) {
            $tags[] = CountryStateRoute::ALL_TAG;
        }

        if (empty($tags)) {
            // invalidates the country-state route when a state changed or an assignment between the state and country changed
            $tags = array_map(
                CountryStateRoute::buildName(...),
                $event->getPrimaryKeys(CountryDefinition::ENTITY_NAME)
            );
        }

        $this->cacheInvalidator->invalidate($tags);
    }

    public function invalidatePaymentMethodRoute(EntityWrittenContainerEvent $event): void
    {
        // invalidates the payment method route when a payment method changed or an assignment between the sales channel and payment method changed
        $logs = [...$this->getChangedPaymentMethods($event), ...$this->getChangedPaymentAssignments($event)];

        $this->cacheInvalidator->invalidate($logs);
    }

    public function invalidateMedia(MediaIndexerEvent $event): void
    {
        /** @var list<array{'product_id':string, 'variant_id':string|null}> $productIds */
        $productIds = $this->connection->fetchAllAssociative(
            'SELECT
                    LOWER(HEX(pm.product_id)) as product_id,
                    IF(variant.id IS NULL,  NULL, LOWER(HEX(variant.id))) as variant_id
                    FROM product_media AS pm
                    LEFT JOIN product as variant ON (pm.product_id = variant.parent_id)
                    WHERE media_id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($event->getIds())],
            ['ids' => ArrayParameterType::STRING]
        );

        $variantIds = array_filter(array_column($productIds, 'variant_id'));
        $uniqueProductIds = array_unique(array_column($productIds, 'product_id'));
        $productIds = array_merge(
            $uniqueProductIds,
            $variantIds,
        );

        $this->cacheInvalidator->invalidate(
            array_map(ProductDetailRoute::buildName(...), $productIds)
        );
    }

    public function invalidateContext(EntityWrittenContainerEvent $event): void
    {
        // invalidates the context cache - each time one of the entities which are considered inside the context factory changed
        $ids = $event->getPrimaryKeys(ChannelDefinition::ENTITY_NAME);
        $keys = array_map(CachedChannelContextFactory::buildName(...), $ids);
        $keys = array_merge($keys, array_map(CachedBaseChannelContextFactory::buildName(...), $ids));

        if ($event->getEventByEntityName(CurrencyDefinition::ENTITY_NAME)) {
            $keys[] = CachedChannelContextFactory::ALL_TAG;
        }

        if ($event->getEventByEntityName(PaymentMethodDefinition::ENTITY_NAME)) {
            $keys[] = CachedChannelContextFactory::ALL_TAG;
        }

        if ($event->getEventByEntityName(CountryDefinition::ENTITY_NAME)) {
            $keys[] = CachedChannelContextFactory::ALL_TAG;
        }

        if ($event->getEventByEntityName(CustomerGroupDefinition::ENTITY_NAME)) {
            $keys[] = CachedChannelContextFactory::ALL_TAG;
        }

        if ($event->getEventByEntityName(LanguageDefinition::ENTITY_NAME)) {
            $keys[] = CachedChannelContextFactory::ALL_TAG;
        }

        /** @var string[] $keys */
        $keys = array_filter(array_unique($keys));

        if (empty($keys)) {
            return;
        }

        // immediately invalidates the context cache
        $this->cacheInvalidator->invalidate($keys, true);
    }

    public function invalidatePropertyFilters(EntityWrittenContainerEvent $event): void
    {
        $this->cacheInvalidator->invalidate([...$this->getChangedPropertyFilterTags($event), ...$this->getDeletedPropertyFilterTags($event)]);
    }

    /**
     * @return string[]
     */
    private function getDeletedPropertyFilterTags(EntityWrittenContainerEvent $event): array
    {
        // invalidates the product listing route, each time a property changed
        $ids = $event->getDeletedPrimaryKeys(ProductPropertyDefinition::ENTITY_NAME);

        if (empty($ids)) {
            return [];
        }

        $productIds = array_column($ids, 'productId');

        return array_merge(
            array_map(ProductDetailRoute::buildName(...), array_unique($productIds)),
        );
    }

    /**
     * @return string[]
     */
    private function getChangedPropertyFilterTags(EntityWrittenContainerEvent $event): array
    {
        // invalidates the product listing route and detail rule, each time a property group changed
        $propertyGroupIds = array_unique(array_merge(
            $event->getPrimaryKeysWithPayloadIgnoringFields(PropertyGroupDefinition::ENTITY_NAME, ['id', 'updatedAt']),
            array_column($event->getPrimaryKeysWithPayloadIgnoringFields(PropertyGroupTranslationDefinition::ENTITY_NAME, ['propertyGroupId', 'languageId', 'updatedAt']), 'propertyGroupId')
        ));

        // invalidates the product listing route and detail rule, each time a property option changed
        $propertyOptionIds = array_unique(array_merge(
            $event->getPrimaryKeysWithPayloadIgnoringFields(PropertyGroupOptionDefinition::ENTITY_NAME, ['id', 'updatedAt']),
            array_column($event->getPrimaryKeysWithPayloadIgnoringFields(PropertyGroupOptionTranslationDefinition::ENTITY_NAME, ['propertyGroupOptionId', 'languageId', 'updatedAt']), 'propertyGroupOptionId')
        ));

        if (empty($propertyGroupIds) && empty($propertyOptionIds)) {
            return [];
        }

        $productIds = $this->connection->fetchFirstColumn(
            'SELECT product_property.product_id
             FROM product_property
                LEFT JOIN property_group_option productProperties ON productProperties.id = product_property.property_group_option_id
             WHERE productProperties.property_group_id IN (:ids) OR productProperties.id IN (:optionIds)
             AND product_property.product_version_id = :version',
            ['ids' => Uuid::fromHexToBytesList($propertyGroupIds), 'optionIds' => Uuid::fromHexToBytesList($propertyOptionIds), 'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            ['ids' => ArrayParameterType::BINARY, 'optionIds' => ArrayParameterType::BINARY]
        );
        $productIds = array_unique([...$productIds, ...$this->connection->fetchFirstColumn(
            'SELECT product_option.product_id
                 FROM product_option
                    LEFT JOIN property_group_option productOptions ON productOptions.id = product_option.property_group_option_id
                 WHERE productOptions.property_group_id IN (:ids) OR productOptions.id IN (:optionIds)
                 AND product_option.product_version_id = :version',
            ['ids' => Uuid::fromHexToBytesList($propertyGroupIds), 'optionIds' => Uuid::fromHexToBytesList($propertyOptionIds), 'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            ['ids' => ArrayParameterType::BINARY, 'optionIds' => ArrayParameterType::BINARY]
        )]);

        if (empty($productIds)) {
            return [];
        }

        $parentIds = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(COALESCE(parent_id, id)))
            FROM product
            WHERE id in (:productIds) AND version_id = :version',
            ['productIds' => $productIds, 'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            ['productIds' => ArrayParameterType::BINARY]
        );

        $categoryIds = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(category_id))
            FROM product_category_tree
            WHERE product_id in (:productIds) AND product_version_id = :version',
            ['productIds' => $productIds, 'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            ['productIds' => ArrayParameterType::BINARY]
        );

        return [
            ...array_map(ProductDetailRoute::buildName(...), array_filter($parentIds)),
        ];
    }

    /**
     * @return list<string>
     */
    private function getChangedPaymentMethods(EntityWrittenContainerEvent $event): array
    {
        $ids = $event->getPrimaryKeys(PaymentMethodDefinition::ENTITY_NAME);
        if (empty($ids)) {
            return [];
        }

        $ids = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(channel_id)) as id FROM channel_payment_method WHERE payment_method_id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::BINARY]
        );

        $tags = [];
        if ($event->getDeletedPrimaryKeys(PaymentMethodDefinition::ENTITY_NAME)) {
            $tags[] = PaymentMethodRoute::ALL_TAG;
        }

        return array_merge($tags, array_map(PaymentMethodRoute::buildName(...), $ids));
    }

    /**
     * @return list<string>
     */
    private function getChangedPaymentAssignments(EntityWrittenContainerEvent $event): array
    {
        // Used to detect changes to the language assignment of a sales channel
        $ids = $event->getPrimaryKeys(ChannelPaymentMethodDefinition::ENTITY_NAME);

        $ids = array_column($ids, 'channelId');

        return array_map(PaymentMethodRoute::buildName(...), $ids);
    }

    /**
     * @return list<string>
     */
    private function getChangedCountries(EntityWrittenContainerEvent $event): array
    {
        $ids = $event->getPrimaryKeys(CountryDefinition::ENTITY_NAME);
        if (empty($ids)) {
            return [];
        }

        // Used to detect changes to the country itself and invalidate the route for all sales channels in which the country is assigned.
        $ids = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(channel_id)) as id FROM channel_country WHERE country_id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::BINARY]
        );

        $tags = [];
        if ($event->getDeletedPrimaryKeys(CountryDefinition::ENTITY_NAME)) {
            $tags[] = CountryRoute::ALL_TAG;
        }

        return array_merge($tags, array_map(CountryRoute::buildName(...), $ids));
    }

    /**
     * @return list<string>
     */
    private function getChangedCountryAssignments(EntityWrittenContainerEvent $event): array
    {
        // Used to detect changes to the country assignment of a sales channel
        $ids = $event->getPrimaryKeys(ChannelCountryDefinition::ENTITY_NAME);

        $ids = array_column($ids, 'channelId');

        return array_map(CountryRoute::buildName(...), $ids);
    }

    /**
     * @return list<string>
     */
    private function getChangedLanguages(EntityWrittenContainerEvent $event): array
    {
        $ids = $event->getPrimaryKeys(LanguageDefinition::ENTITY_NAME);
        if (empty($ids)) {
            return [];
        }

        // Used to detect changes to the language itself and invalidate the route for all sales channels in which the language is assigned.
        $ids = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(channel_id)) as id FROM channel_language WHERE language_id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::BINARY]
        );

        $tags = [];
        if ($event->getDeletedPrimaryKeys(LanguageDefinition::ENTITY_NAME)) {
            $tags[] = LanguageRoute::ALL_TAG;
        }

        return array_merge($tags, array_map(LanguageRoute::buildName(...), $ids));
    }

    /**
     * @return list<string>
     */
    private function getChangedLanguageAssignments(EntityWrittenContainerEvent $event): array
    {
        // Used to detect changes to the language assignment of a sales channel
        $ids = $event->getPrimaryKeys(ChannelLanguageDefinition::ENTITY_NAME);

        $ids = array_column($ids, 'channelId');

        return array_map(LanguageRoute::buildName(...), $ids);
    }

    /**
     * @return list<string>
     */
    private function getChangedCurrencies(EntityWrittenContainerEvent $event): array
    {
        $ids = $event->getPrimaryKeys(CurrencyDefinition::ENTITY_NAME);

        if (empty($ids)) {
            return [];
        }

        // Used to detect changes to the currency itself and invalidate the route for all sales channels in which the currency is assigned.
        $ids = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(channel_id)) as id FROM channel_currency WHERE currency_id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::BINARY]
        );

        $tags = [];
        if ($event->getDeletedPrimaryKeys(CurrencyDefinition::ENTITY_NAME)) {
            $tags[] = CurrencyRoute::ALL_TAG;
        }

        return array_merge($tags, array_map(CurrencyRoute::buildName(...), $ids));
    }

    /**
     * @return list<string>
     */
    private function getChangedCurrencyAssignments(EntityWrittenContainerEvent $event): array
    {
        // Used to detect changes to the currency assignment of a sales channel
        $ids = $event->getPrimaryKeys(ChannelCurrencyDefinition::ENTITY_NAME);

        $ids = array_column($ids, 'channelId');

        return array_map(CurrencyRoute::buildName(...), $ids);
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string>
     */
    private function getParentIds(array $ids): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(COALESCE(parent_id, id))) as id FROM product WHERE id IN (:ids) AND version_id = :version',
            ['ids' => Uuid::fromHexToBytesList($ids), 'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            ['ids' => ArrayParameterType::BINARY]
        );
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string>
     */
    private function getSetIds(array $ids): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(snippet_set_id)) FROM snippet WHERE id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::BINARY]
        );
    }
}
