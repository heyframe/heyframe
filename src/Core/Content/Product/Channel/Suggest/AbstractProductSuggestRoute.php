<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Suggest;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route is used for the product suggest in the page header
 */
#[Package('discovery')]
abstract class AbstractProductSuggestRoute
{
    abstract public function getDecorated(): AbstractProductSuggestRoute;

    abstract public function load(Request $request, ChannelContext $context, Criteria $criteria): ProductSuggestRouteResponse;
}
