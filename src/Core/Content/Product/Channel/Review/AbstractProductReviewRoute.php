<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('after-sales')]
abstract class AbstractProductReviewRoute
{
    abstract public function getDecorated(): AbstractProductReviewRoute;

    abstract public function load(string $productId, Request $request, ChannelContext $context, Criteria $criteria): ProductReviewRouteResponse;
}
