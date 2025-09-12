<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing\Processor;

use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
class PagingListingProcessor extends AbstractListingProcessor
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $config,
        private readonly int $maxLimit = 100
    ) {
    }

    public function getDecorated(): AbstractListingProcessor
    {
        throw new DecorationPatternException(self::class);
    }

    public function prepare(Request $request, Criteria $criteria, ChannelContext $context): void
    {
        $limit = $this->getLimit($criteria, $context, $request);

        $page = $this->getPage($request);
        if ($page !== null) {
            $criteria->setOffset(($page - 1) * $limit);
        }
        if ($criteria->getOffset() === null || $criteria->getOffset() < 0) {
            $criteria->setOffset(0);
        }

        $criteria->setLimit($limit);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
    }

    public function process(Request $request, ProductListingResult $result, ChannelContext $context): void
    {
        $page = $this->getPage($request);
        if ($page !== null) {
            $result->setPage($page);
        }

        $limit = $result->getCriteria()->getLimit() ?? $this->getLimit($result->getCriteria(), $context, $request);
        $result->setLimit($limit);
    }

    private function getLimit(Criteria $criteria, ChannelContext $context, Request $request): int
    {
        $limit = $request->query->has('limit') ? $request->query->getInt('limit') : null;
        $limit = $request->request->has('limit') ? $request->request->getInt('limit') : $limit;

        // request > criteria > config
        if ($limit > 0) {
            return min($limit, $this->maxLimit);
        }

        if ($criteria->getLimit() !== null && $criteria->getLimit() > 0) {
            return min($criteria->getLimit(), $this->maxLimit);
        }

        $limit = $this->config->getInt('core.listing.productsPerPage', $context->getChannelId());

        return $limit <= 0 ? 24 : min($limit, $this->maxLimit);
    }

    private function getPage(Request $request): ?int
    {
        $page = $request->query->has('p') ? $request->query->getInt('p') : null;
        $page = $request->request->has('p') ? $request->request->getInt('p') : $page;

        return $page > 0 ? $page : null;
    }
}
