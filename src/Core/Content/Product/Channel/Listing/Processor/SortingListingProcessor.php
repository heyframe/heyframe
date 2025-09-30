<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing\Processor;

use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Content\Product\Channel\Sorting\ProductSortingCollection;
use HeyFrame\Core\Content\Product\Channel\Sorting\ProductSortingEntity;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
class SortingListingProcessor extends AbstractListingProcessor
{
    /**
     * @param EntityRepository<ProductSortingCollection> $sortingRepository
     *
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $sortingRepository
    ) {
    }

    public function getDecorated(): AbstractListingProcessor
    {
        throw new DecorationPatternException(self::class);
    }

    public function prepare(Request $request, Criteria $criteria, ChannelContext $context): void
    {
        if (!$request->get('order')) {
            $key = $request->get('search') ? 'core.listing.defaultSearchResultSorting' : 'core.listing.defaultSorting';
            $request->request->set('order', $this->getDefaultSortingKey($key, $context));
        }

        /** @var ProductSortingCollection $sortings */
        $sortings = $criteria->getExtension('sortings') ?? new ProductSortingCollection();
        $sortings->merge($this->getAvailableSortings($request, $context->getContext()));

        $currentSorting = $this->getCurrentSorting($sortings, $request, $context->getChannelId());

        if ($currentSorting !== null) {
            $fallbackSorting = null;
            if ($this->hasQueriesOrTerm($criteria)) {
                $fallbackSorting = new FieldSorting('_score', FieldSorting::DESCENDING);
            }

            $criteria->addSorting(
                ...$currentSorting->createDalSorting($fallbackSorting)
            );
        }

        $criteria->addExtension('sortings', $sortings);
    }

    public function process(Request $request, ProductListingResult $result, ChannelContext $context): void
    {
        /** @var ProductSortingCollection $sortings */
        $sortings = $result->getCriteria()->getExtension('sortings');
        $currentSorting = $this->getCurrentSorting($sortings, $request, $context->getChannelId());

        if ($currentSorting !== null) {
            $result->setSorting($currentSorting->getKey());
        }

        $result->setAvailableSortings($sortings);
    }

    private function hasQueriesOrTerm(Criteria $criteria): bool
    {
        return !empty($criteria->getQueries()) || $criteria->getTerm();
    }

    private function getCurrentSorting(ProductSortingCollection $sortings, Request $request, string $salesChannelId): ?ProductSortingEntity
    {
        $key = $request->get('order');

        if (!\is_string($key)) {
            throw ProductException::sortingNotFoundException('');
        }

        $sorting = $sortings->getByKey($key);
        if ($sorting !== null) {
            return $sorting;
        }

        return $sortings->get($this->systemConfigService->getString('core.listing.defaultSorting', $salesChannelId));
    }

    private function getAvailableSortings(Request $request, Context $context): ProductSortingCollection
    {
        $criteria = new Criteria();
        $criteria->setTitle('product-listing::load-sortings');
        /** @var string[] $availableSortings */
        $availableSortings = $request->get('availableSortings');
        $availableSortingsById = [];

        if ($availableSortings) {
            arsort($availableSortings, \SORT_DESC | \SORT_NUMERIC);
            $availableSortingsFilter = array_keys($availableSortings);

            $availableSortingsById = array_filter($availableSortingsFilter, fn ($filter) => Uuid::isValid($filter));

            $filter = new EqualsAnyFilter('id', $availableSortingsById);

            $criteria->addFilter($filter);
        }

        $criteria
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('priority', 'DESC'));

        $sortings = $this->sortingRepository->search($criteria, $context)->getEntities();

        if ($availableSortingsById) {
            $sortings->sortByIdArray($availableSortingsById);
        }

        return $sortings;
    }

    private function getDefaultSortingKey(string $key, ChannelContext $context): ?string
    {
        $id = $this->systemConfigService->getString($key, $context->getChannelId());

        if (!Uuid::isValid($id)) {
            return $id;
        }

        $criteria = new Criteria([$id]);

        return $this->sortingRepository->search($criteria, $context->getContext())->first()?->get('key');
    }
}
