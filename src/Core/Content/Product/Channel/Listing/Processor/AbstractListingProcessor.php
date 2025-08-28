<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing\Processor;

use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
abstract class AbstractListingProcessor
{
    abstract public function getDecorated(): self;

    /**
     * The `prepare` function allows to take care of the request parameters and interpret the different query and post
     * parameters and apply them to the provided `Criteria` object.
     *
     * The function is used in different contexts, like search, suggest and listing. You can check the different context by checking
     * the `criteria.states` collection for:
     * - 'suggest-route-context'
     * - 'listing-route-context'
     * - 'search-route-context'
     */
    abstract public function prepare(Request $request, Criteria $criteria, ChannelContext $context): void;

    /**
     * The `process` function allows to post process the determined listing result and enrich the result with more
     * meta information or to further process it for more user readable data.
     *
     * The function is used in different contexts, like search, suggest and listing. You can check the different context by checking
     * the `criteria.states` collection for:
     * - 'suggest-route-context'
     * - 'listing-route-context'
     * - 'search-route-context'
     */
    public function process(Request $request, ProductListingResult $result, ChannelContext $context): void
    {
    }
}
