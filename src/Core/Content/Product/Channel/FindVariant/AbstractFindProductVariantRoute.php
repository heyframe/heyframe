<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\FindVariant;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route is used the search for a matching product variant by given options
 */
#[Package('inventory')]
abstract class AbstractFindProductVariantRoute
{
    abstract public function getDecorated(): AbstractFindProductVariantRoute;

    abstract public function load(string $productId, Request $request, ChannelContext $context): FindProductVariantRouteResponse;
}
