<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing\Filter;

use HeyFrame\Core\Content\Product\Channel\Listing\Filter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\MaxAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
class RatingListingFilterHandler extends AbstractListingFilterHandler
{
    final public const FILTER_ENABLED_REQUEST_PARAM = 'rating-filter';

    public function getDecorated(): AbstractListingFilterHandler
    {
        throw new DecorationPatternException(self::class);
    }

    public function create(Request $request, ChannelContext $context): ?Filter
    {
        if (!$request->request->get(self::FILTER_ENABLED_REQUEST_PARAM, true)) {
            return null;
        }

        $filtered = $request->get('rating');

        return new Filter(
            'rating',
            $filtered !== null,
            [
                new FilterAggregation(
                    'rating-exists',
                    new MaxAggregation('rating', 'product.ratingAverage'),
                    [new RangeFilter('product.ratingAverage', [RangeFilter::GTE => 0])]
                ),
            ],
            new RangeFilter('product.ratingAverage', [
                RangeFilter::GTE => (int) $filtered,
            ]),
            $filtered
        );
    }
}
