<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Service;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;

/**
 * @internal
 */
#[Package('discovery')]
class DefaultCategoryLevelLoader implements DefaultCategoryLevelLoaderInterface
{
    /**
     * @param ChannelRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(
        private readonly ChannelRepository $categoryRepository,
    ) {
    }

    public function loadLevels(
        string $rootId,
        int $rootLevel,
        ChannelContext $context,
        Criteria $criteria,
        int $depth,
    ): CategoryCollection {
        $criteria->addFilter(new OrFilter(
            [
                new EqualsFilter('id', $rootId),
                new AndFilter([
                    new ContainsFilter('path', '|' . $rootId . '|'),
                    new RangeFilter('level', [
                        RangeFilter::GT => $rootLevel,
                        RangeFilter::LTE => $rootLevel + $depth + 1,
                    ]),
                ]),
            ]
        ));

        $criteria->addAssociation('media');

        $criteria->setLimit(null);

        $levels = $this->categoryRepository->search($criteria, $context)->getEntities();

        $this->addVisibilityCounts($rootId, $rootLevel, $depth, $levels, $context);

        return $levels;
    }

    private function addVisibilityCounts(string $rootId, int $rootLevel, int $depth, CategoryCollection $levels, ChannelContext $context): void
    {
        $counts = [];
        foreach ($levels as $category) {
            if (!$category->getActive() || !$category->getVisible()) {
                continue;
            }

            $parentId = $category->getParentId();
            $counts[$parentId] ??= 0;
            ++$counts[$parentId];
        }
        foreach ($levels as $category) {
            $category->setVisibleChildCount($counts[$category->getId()] ?? 0);
        }

        // Fetch additional level of categories for counting visible children that are NOT included in the original query
        $criteria = new Criteria();
        $criteria->addFilter(
            new ContainsFilter('path', '|' . $rootId . '|'),
            new EqualsFilter('level', $rootLevel + $depth + 1),
            new EqualsFilter('active', true),
            new EqualsFilter('visible', true)
        );

        $criteria->addAggregation(
            new TermsAggregation('category-ids', 'parentId', null, null, new CountAggregation('visible-children-count', 'id'))
        );

        $termsResult = $this->categoryRepository
            ->aggregate($criteria, $context)
            ->get('category-ids');

        if (!($termsResult instanceof TermsResult)) {
            return;
        }

        foreach ($termsResult->getBuckets() as $bucket) {
            $key = $bucket->getKey();

            if ($key === null) {
                continue;
            }

            $parent = $levels->get($key);

            if ($parent instanceof CategoryEntity) {
                $parent->setVisibleChildCount($bucket->getCount());
            }
        }
    }
}
