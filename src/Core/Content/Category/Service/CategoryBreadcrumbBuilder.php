<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Breadcrumb\BreadcrumbException;
use HeyFrame\Core\Content\Breadcrumb\Struct\Breadcrumb;
use HeyFrame\Core\Content\Breadcrumb\Struct\BreadcrumbCollection;
use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Content\Product\Channel\ChannelProductCollection;
use HeyFrame\Core\Content\Product\Channel\ChannelProductEntity;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Seo\MainCategory\MainCategoryEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Storefront\Framework\Seo\SeoUrlRoute\NavigationPageSeoUrlRoute;

#[Package('discovery')]
class CategoryBreadcrumbBuilder
{
    /**
     * @internal
     *
     * @param EntityRepository<CategoryCollection> $categoryRepository
     * @param ChannelRepository<ChannelProductCollection> $productRepository
     */
    public function __construct(
        private readonly EntityRepository $categoryRepository,
        private readonly ChannelRepository $productRepository,
        private readonly Connection $connection
    ) {
    }

    public function getProductBreadcrumbUrls(string $productId, string $referrerCategoryId, ChannelContext $channelContext): BreadcrumbCollection
    {
        $product = $this->loadProduct($productId, $channelContext);
        $category = $this->getCategoryForProduct($referrerCategoryId, $product, $channelContext);
        if ($category === null) {
            throw BreadcrumbException::categoryNotFoundForProduct($productId);
        }

        return $this->getCategoryBreadcrumbUrls(
            $category,
            $channelContext->getContext(),
            $channelContext->getChannel()
        );
    }

    public function loadCategory(string $categoryId, Context $context): ?CategoryEntity
    {
        $criteria = new Criteria([$categoryId]);
        $criteria->setTitle('breadcrumb::category::data');

        $category = $this->categoryRepository
            ->search($criteria, $context)
            ->get($categoryId);

        if (!$category instanceof CategoryEntity) {
            return null;
        }

        return $category;
    }

    public function getProductSeoCategory(ProductEntity $product, ChannelContext $context): ?CategoryEntity
    {
        $category = $this->getMainCategory($product, $context);
        if ($category !== null) {
            return $category;
        }

        $categoryIds = $product->getCategoryIds() ?? [];
        $productStreamIds = $product->getStreamIds() ?? [];

        if (empty($productStreamIds) && empty($categoryIds)) {
            return null;
        }

        $criteria = new Criteria();
        $criteria->setTitle('breadcrumb-builder');
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('active', true));

        if (!empty($categoryIds)) {
            $criteria->setIds($categoryIds);
        } else {
            $criteria->addFilter(new EqualsAnyFilter('productStream.id', $productStreamIds));
            $criteria->addFilter(new EqualsFilter('productAssignmentType', CategoryDefinition::PRODUCT_ASSIGNMENT_TYPE_PRODUCT_STREAM));
        }

        $criteria->addFilter($this->getChannelFilter($context->getChannel()));

        $categories = $this->categoryRepository->search($criteria, $context->getContext())->getEntities();
        if ($categories->count() > 0) {
            return $categories->first();
        }

        return null;
    }

    public function getCategoryBreadcrumbUrls(CategoryEntity $category, Context $context, ChannelEntity $channel): BreadcrumbCollection
    {
        $seoBreadcrumb = $this->build($category, $channel);
        $categoryIds = array_keys($seoBreadcrumb ?? []);

        if (empty($categoryIds)) {
            return new BreadcrumbCollection();
        }

        $categories = $this->loadCategories($categoryIds, $context, $channel);
        $seoUrls = $this->loadSeoUrls($categoryIds, $context, $channel);

        return $this->convertCategoriesToBreadcrumbUrls($categories, $seoUrls);
    }

    /**
     * @return array<string, string>|null
     */
    public function build(CategoryEntity $category, ?ChannelEntity $channel = null, ?string $navigationCategoryId = null): ?array
    {
        $categoryBreadcrumb = $category->getPlainBreadcrumb();

        // If the current Channel is null ( which refers to the default template Channel) or
        // this category has no root, we return the full breadcrumb
        if ($channel === null && $navigationCategoryId === null) {
            return $categoryBreadcrumb;
        }

        $entryPoints = [
            $navigationCategoryId,
        ];

        if ($channel !== null) {
            $entryPoints[] = $channel->getNavigationCategoryId();
            $entryPoints[] = $channel->getServiceCategoryId();
            $entryPoints[] = $channel->getFooterCategoryId();
        }

        $entryPoints = array_filter($entryPoints);

        $keys = array_keys($categoryBreadcrumb);

        foreach ($entryPoints as $entryPoint) {
            // Check where this category is located in relation to the navigation entry point of the sales channel
            $pos = array_search($entryPoint, $keys, true);

            if ($pos !== false) {
                // Remove all breadcrumbs preceding the navigation category
                return \array_slice($categoryBreadcrumb, $pos + 1);
            }
        }

        return $categoryBreadcrumb;
    }

    private function loadProduct(string $productId, ChannelContext $channelContext): ChannelProductEntity
    {
        $criteria = new Criteria();
        $criteria->setIds([$productId]);
        $criteria->setTitle('breadcrumb::product::data');

        $product = $this->productRepository
            ->search($criteria, $channelContext)
            ->first();

        if (!($product instanceof ChannelProductEntity)) {
            throw BreadcrumbException::productNotFound($productId);
        }

        return $product;
    }

    private function getCategoryForProduct(
        string $referrerCategoryId,
        ChannelProductEntity $product,
        ChannelContext $channelContext
    ): ?CategoryEntity {
        $categoryIds = $product->getCategoryIds();
        if ($categoryIds !== null && \in_array($referrerCategoryId, $categoryIds, true)) {
            return $this->loadCategory($referrerCategoryId, $channelContext->getContext());
        }

        return $this->getProductSeoCategory($product, $channelContext);
    }

    private function getMainCategory(ProductEntity $product, ChannelContext $context): ?CategoryEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->setTitle('breadcrumb-builder::main-category');

        if (($product->getMainCategories() === null || $product->getMainCategories()->count() <= 0) && $product->getParentId() !== null) {
            $criteria->addFilter($this->getMainCategoryFilter($product->getParentId(), $context));
        } else {
            $criteria->addFilter($this->getMainCategoryFilter($product->getId(), $context));
        }

        $categories = $this->categoryRepository->search($criteria, $context->getContext())->getEntities();
        if ($categories->count() <= 0) {
            return null;
        }

        $firstCategory = $categories->first();

        $entity = $firstCategory instanceof MainCategoryEntity ? $firstCategory->getCategory() : $firstCategory;

        return $product->getCategoryIds() !== null && $entity !== null && \in_array($entity->getId(), $product->getCategoryIds(), true) ? $entity : null;
    }

    private function getMainCategoryFilter(string $productId, ChannelContext $context): AndFilter
    {
        return new AndFilter([
            new EqualsFilter('mainCategories.productId', $productId),
            new EqualsFilter('mainCategories.channelId', $context->getChannelId()),
            $this->getChannelFilter($context->getChannel()),
        ]);
    }

    private function getChannelFilter(ChannelEntity $channel): MultiFilter
    {
        $ids = array_filter([
            $channel->getNavigationCategoryId(),
            $channel->getServiceCategoryId(),
            $channel->getFooterCategoryId(),
        ]);

        return new OrFilter(array_map(static fn (string $id) => new ContainsFilter('path', '|' . $id . '|'), $ids));
    }

    /**
     * @param array<string> $categoryIds
     */
    private function loadCategories(array $categoryIds, Context $context, ChannelEntity $channel): CategoryCollection
    {
        $criteria = new Criteria($categoryIds);
        $criteria->setTitle('breadcrumb::categories::data');
        $criteria->addFilter($this->getChannelFilter($channel));

        return $this->categoryRepository->search($criteria, $context)->getEntities();
    }

    /**
     * @param array<string> $categoryIds
     *
     * @return array<int, array<string, string|mixed>>
     */
    private function loadSeoUrls(array $categoryIds, Context $context, ChannelEntity $channel): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'LOWER(HEX(id)) as id',
            'LOWER(HEX(foreign_key)) as categoryId',
            'path_info as pathInfo',
            'seo_path_info as seoPathInfo',
        );
        $query->from('seo_url');
        $query->where('seo_url.is_canonical = 1');
        $query->andWhere('seo_url.route_name = :routeName');
        $query->andWhere('seo_url.language_id = :languageId');
        $query->andWhere('seo_url.channel_id = :channelId');
        $query->andWhere('seo_url.foreign_key IN (:categoryIds)');
        $query->setParameter('routeName', NavigationPageSeoUrlRoute::ROUTE_NAME);
        $query->setParameter('languageId', Uuid::fromHexToBytes($context->getLanguageId()));
        $query->setParameter('channelId', Uuid::fromHexToBytes($channel->getId()));
        $query->setParameter('categoryIds', Uuid::fromHexToBytesList($categoryIds), ArrayParameterType::BINARY);

        return $query->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param array<int, array<string, string|mixed>> $seoUrls
     */
    private function convertCategoriesToBreadcrumbUrls(CategoryCollection $categories, array $seoUrls): BreadcrumbCollection
    {
        $seoBreadcrumbCollection = [];
        foreach ($categories as $category) {
            $categoryId = $category->getId();
            $categorySeoUrls = $this->filterCategorySeoUrls($seoUrls, $categoryId);
            $translated = $category->getTranslated();
            unset($translated['breadcrumb'], $translated['name']);
            $categoryBreadcrumb = new Breadcrumb(
                $category->getTranslation('name'),
                $categoryId,
                $category->getType(),
                $translated,
            );

            if (!$categorySeoUrls || \count($categorySeoUrls) === 0) {
                $categoryBreadcrumb->path = 'navigation/' . $categoryId;
                continue;
            }

            foreach ($categorySeoUrls as $categorySeoUrl) {
                if ($categoryBreadcrumb->path === '') {
                    $categoryBreadcrumb->path = (isset($categorySeoUrl['seoPathInfo']) && $categorySeoUrl['seoPathInfo'] !== '')
                        ? $categorySeoUrl['seoPathInfo'] : $categorySeoUrl['pathInfo'];
                }
                if ($categoryId === $categorySeoUrl['categoryId']) {
                    unset($categorySeoUrl['categoryId']); // remove redundant data
                }
                $categoryBreadcrumb->seoUrls[] = $categorySeoUrl;
            }

            $seoBreadcrumbCollection[$categoryId] = $categoryBreadcrumb;
        }

        return new BreadcrumbCollection(array_values($seoBreadcrumbCollection));
    }

    /**
     * @param array<int, array<string, string|mixed>> $seoUrls
     *
     * @return array<int, array<string, string|mixed>>
     */
    private function filterCategorySeoUrls(array $seoUrls, string $categoryId): array
    {
        return array_filter($seoUrls, function (array $seoUrl) use ($categoryId) {
            return $seoUrl['categoryId'] === $categoryId;
        });
    }
}
