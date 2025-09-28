<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Product\Channel\AbstractProductCloseoutFilterFactory;
use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Content\Product\Channel\Search\ResolvedCriteriaProductSearchRoute;
use HeyFrame\Core\Content\Product\Channel\Suggest\ProductSuggestRoute;
use HeyFrame\Core\Content\Product\Events\ProductListingPreviewCriteriaEvent;
use HeyFrame\Core\Content\Product\Events\ProductListingResolvePreviewEvent;
use HeyFrame\Core\Content\Product\Extension\LoadPreviewExtension;
use HeyFrame\Core\Content\Product\Extension\ResolveListingExtension;
use HeyFrame\Core\Content\Product\Extension\ResolveListingIdsExtension;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayEntity;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('inventory')]
class ProductListingLoader
{
    /**
     * @internal
     *
     * @param ChannelRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly ChannelRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory,
        private readonly ExtensionDispatcher $extensions
    ) {
    }

    /**
     * @return EntitySearchResult<ProductCollection>
     */
    public function load(Criteria $origin, ChannelContext $context): EntitySearchResult
    {
        // allows full service decoration
        return $this->extensions->publish(
            name: ResolveListingExtension::NAME,
            extension: new ResolveListingExtension($origin, $context),
            function: $this->_load(...)
        );
    }

    /**
     * @return EntitySearchResult<ProductCollection>
     */
    private function _load(Criteria $criteria, ChannelContext $context): EntitySearchResult
    {
        $criteria->addState(Criteria::STATE_ELASTICSEARCH_AWARE);
        $clone = clone $criteria;

        $idResult = $this->extensions->publish(
            name: ResolveListingIdsExtension::NAME,
            extension: new ResolveListingIdsExtension($clone, $context),
            function: $this->resolveIds(...)
        );

        $aggregations = $this->productRepository->aggregate($clone, $context);

        /** @var list<string> $ids */
        $ids = $idResult->getIds();
        // no products found, no need to continue
        if (empty($ids)) {
            $result = new EntitySearchResult(
                ProductDefinition::ENTITY_NAME,
                0,
                new ProductCollection(),
                $aggregations,
                $criteria,
                $context->getContext()
            );

            $result->addState(...$idResult->getStates());
            $result->addExtensions($idResult->getExtensions());

            return $result;
        }

        $mapping = $this->resolvePreviews($ids, $clone, $context);

        $productSearchResult = $this->resolveData($clone, $mapping, $context);

        $this->addExtensions($clone, $idResult, $productSearchResult, $mapping);

        $result = new EntitySearchResult(ProductDefinition::ENTITY_NAME, $idResult->getTotal(), $productSearchResult->getEntities(), $aggregations, $criteria, $context->getContext());
        $result->addState(...$idResult->getStates());
        $result->addExtensions($productSearchResult->getExtensions());

        return $result;
    }

    private function hasOptionFilter(Criteria $criteria): bool
    {
        $filters = $criteria->getPostFilters();

        $fields = [];
        foreach ($filters as $filter) {
            array_push($fields, ...$filter->getFields());
        }

        $fields = array_map(fn (string $field) => preg_replace('/^product./', '', $field), $fields);

        if (\in_array('options.id', $fields, true)) {
            return true;
        }

        if (\in_array('optionIds', $fields, true)) {
            return true;
        }

        return false;
    }

    private function addGrouping(Criteria $criteria): void
    {
        $criteria->addGroupField(new FieldGrouping('displayGroup'));
        $criteria->addFilter(new NotEqualsFilter('displayGroup', null));
    }

    /**
     * @param array<string> $ids
     *
     * @throws \JsonException
     *
     * @return array<string>
     */
    private function loadPreviews(array $ids, ChannelContext $context): array
    {
        $ids = array_combine($ids, $ids);

        $config = $this->connection->fetchAllAssociative(
            '# product-listing-loader::resolve-previews
            SELECT
                parent.variant_listing_config as variantListingConfig,
                LOWER(HEX(child.id)) as id,
                LOWER(HEX(parent.id)) as parentId
             FROM product as child
                INNER JOIN product as parent
                    ON parent.id = child.parent_id
                    AND parent.version_id = child.version_id
             WHERE child.version_id = :version
             AND child.id IN (:ids)',
            [
                'ids' => Uuid::fromHexToBytesList(array_values($ids)),
                'version' => Uuid::fromHexToBytes($context->getContext()->getVersionId()),
            ],
            ['ids' => ArrayParameterType::BINARY]
        );

        $mapping = [];
        foreach ($config as $item) {
            if ($item['variantListingConfig'] === null) {
                continue;
            }
            $variantListingConfig = json_decode((string) $item['variantListingConfig'], true, 512, \JSON_THROW_ON_ERROR);

            if (isset($variantListingConfig['mainVariantId']) && $variantListingConfig['mainVariantId']) {
                $mapping[$item['id']] = $variantListingConfig['mainVariantId'];
            }

            if (isset($variantListingConfig['displayParent']) && $variantListingConfig['displayParent']) {
                $mapping[$item['id']] = $item['parentId'];
            }
        }

        // now we have a mapping for "child => main variant"
        if (empty($mapping)) {
            return $ids;
        }

        // filter inactive and not available variants
        $criteria = new Criteria(array_values($mapping));
        $criteria->addFilter(new ProductAvailableFilter($context->getChannelId()));

        if ($this->systemConfigService->getBool(
            'core.listing.hideCloseoutProductsWhenOutOfStock',
            $context->getChannelId()
        )) {
            $criteria->addFilter(
                $this->productCloseoutFilterFactory->create($context)
            );
        }

        $this->dispatcher->dispatch(
            new ProductListingPreviewCriteriaEvent($criteria, $context)
        );

        $available = $this->productRepository->searchIds($criteria, $context);

        $remapped = [];
        // replace existing ids with main variant id
        foreach ($ids as $id) {
            // id has no mapped main_variant - keep old id
            if (!isset($mapping[$id])) {
                $remapped[$id] = $id;

                continue;
            }

            // get access to main variant id over the fetched config mapping
            $main = $mapping[$id];

            // main variant is configured but not active/available - keep old id
            if (!$available->has($main)) {
                $remapped[$id] = $id;

                continue;
            }

            // main variant is configured and available - add main variant id
            $remapped[$id] = $main;
        }

        return $remapped;
    }

    /**
     * @param EntitySearchResult<ProductCollection> $productSearchResult
     * @param array<string> $mapping
     */
    private function addExtensions(Criteria $criteria, IdSearchResult $ids, EntitySearchResult $productSearchResult, array $mapping): void
    {
        foreach ($ids->getExtensions() as $name => $extension) {
            $productSearchResult->addExtension($name, $extension);
        }

        if ($criteria->hasState(Criteria::STATE_DISABLE_SEARCH_INFO)) {
            return;
        }

        /** @var string $id */
        foreach ($ids->getIds() as $id) {
            if (!isset($mapping[$id])) {
                continue;
            }

            // current id was mapped to another variant
            if (!$productSearchResult->has($mapping[$id])) {
                continue;
            }

            $product = $productSearchResult->get($mapping[$id]);

            // get access to the data of the search result
            $product->addExtension('search', new ArrayEntity($ids->getDataOfId($id)));
        }
    }

    private function resolveIds(Criteria $criteria, ChannelContext $context): IdSearchResult
    {
        $this->addGrouping($criteria);

        if ($this->systemConfigService->getBool(
            'core.listing.hideCloseoutProductsWhenOutOfStock',
            $context->getChannelId()
        )) {
            $criteria->addFilter(
                $this->productCloseoutFilterFactory->create($context)
            );
        }

        return $this->productRepository->searchIds($criteria, $context);
    }

    /**
     * @param list<string> $keys
     *
     * @return array<string, string>
     */
    private function resolvePreviews(array $keys, Criteria $criteria, ChannelContext $context): array
    {
        $mapping = array_combine($keys, $keys);

        $hasOptionFilter = $this->hasOptionFilter($criteria);

        $shouldLoadPreviews = $this->shouldLoadPreviews($hasOptionFilter, $criteria);

        if ($shouldLoadPreviews) {
            $mapping = $this->extensions->publish(
                name: LoadPreviewExtension::NAME,
                extension: new LoadPreviewExtension($keys, $context),
                function: $this->loadPreviews(...)
            );
        }

        $event = new ProductListingResolvePreviewEvent($context, $criteria, $mapping, $hasOptionFilter);
        $this->dispatcher->dispatch($event);

        return $event->getMapping();
    }

    private function shouldLoadPreviews(bool $hasOptionFilter, Criteria $criteria): bool
    {
        if ($hasOptionFilter === true) {
            return false;
        }

        return !$criteria->hasState(ResolvedCriteriaProductSearchRoute::STATE, ProductSuggestRoute::STATE);
    }

    /**
     * @param array<string, string> $mapping
     *
     * @return EntitySearchResult<ProductCollection>
     */
    private function resolveData(Criteria $criteria, array $mapping, ChannelContext $context): EntitySearchResult
    {
        $read = $criteria->cloneForRead(array_values($mapping));
        $read->addAssociation('options.group');

        return $this->productRepository->search($read, $context);
    }
}
