<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route is used for the product listing in the cms pages
 */
#[Package('inventory')]
abstract class AbstractProductListingRoute
{
    abstract public function getDecorated(): AbstractProductListingRoute;

    abstract public function load(string $categoryId, Request $request, ChannelContext $context, Criteria $criteria): ProductListingRouteResponse;
}
