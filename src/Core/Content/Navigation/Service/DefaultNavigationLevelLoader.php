<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Service;

use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
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

/**
 * @internal
 */
#[Package('discovery')]
class DefaultNavigationLevelLoader implements DefaultNavigationLevelLoaderInterface
{
    /**
     * @param EntityRepository<NavigationCollection> $navigationRepository
     */
    public function __construct(
        private readonly EntityRepository $navigationRepository,
    ) {
    }

    public function loadLevels(
        string $rootId,
        int $rootLevel,
        ChannelContext $context,
        Criteria $criteria,
        int $depth,
    ): NavigationCollection {
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

        $levels = $this->navigationRepository->search($criteria, $context)->getEntities();

        $this->addVisibilityCounts($rootId, $rootLevel, $depth, $levels, $context);

        return $levels;
    }

    private function addVisibilityCounts(string $rootId, int $rootLevel, int $depth, NavigationCollection $levels, ChannelContext $context): void
    {
        $counts = [];
        foreach ($levels as $navigation) {
            if (!$navigation->getActive() || !$navigation->getVisible()) {
                continue;
            }

            $parentId = $navigation->getParentId();
            $counts[$parentId] ??= 0;
            ++$counts[$parentId];
        }
        foreach ($levels as $navigation) {
            $navigation->setVisibleChildCount($counts[$navigation->getId()] ?? 0);
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
            new TermsAggregation('navigation-ids', 'parentId', null, null, new CountAggregation('visible-children-count', 'id'))
        );

        $termsResult = $this->navigationRepository
            ->aggregate($criteria, $context)
            ->get('navigation-ids');

        if (!($termsResult instanceof TermsResult)) {
            return;
        }

        foreach ($termsResult->getBuckets() as $bucket) {
            $key = $bucket->getKey();

            if ($key === null) {
                continue;
            }

            $parent = $levels->get($key);

            if ($parent instanceof NavigationEntity) {
                $parent->setVisibleChildCount($bucket->getCount());
            }
        }
    }
}
