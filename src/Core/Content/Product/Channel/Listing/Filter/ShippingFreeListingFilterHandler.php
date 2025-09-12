<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing\Filter;

use HeyFrame\Core\Content\Product\Channel\Listing\Filter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\MaxAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
class ShippingFreeListingFilterHandler extends AbstractListingFilterHandler
{
    final public const FILTER_ENABLED_REQUEST_PARAM = 'shipping-free-filter';

    public function getDecorated(): AbstractListingFilterHandler
    {
        throw new DecorationPatternException(self::class);
    }

    public function create(Request $request, ChannelContext $context): ?Filter
    {
        if (!$request->request->get(self::FILTER_ENABLED_REQUEST_PARAM, true)) {
            return null;
        }

        $filtered = (bool) $request->get('shipping-free', false);

        return new Filter(
            'shipping-free',
            $filtered === true,
            [
                new FilterAggregation(
                    'shipping-free-filter',
                    new MaxAggregation('shipping-free', 'product.shippingFree'),
                    [new EqualsFilter('product.shippingFree', true)]
                ),
            ],
            new EqualsFilter('product.shippingFree', true),
            $filtered
        );
    }
}
