<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\CrossSelling;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route will be used to load all cross-selling lists of the provided product id
 */
#[Package('inventory')]
abstract class AbstractProductCrossSellingRoute
{
    abstract public function getDecorated(): AbstractProductCrossSellingRoute;

    abstract public function load(string $productId, Request $request, ChannelContext $context, Criteria $criteria): ProductCrossSellingRouteResponse;
}
