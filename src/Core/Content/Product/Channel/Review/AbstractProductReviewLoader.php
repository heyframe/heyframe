<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('after-sales')]
abstract class AbstractProductReviewLoader
{
    abstract public function getDecorated(): AbstractProductReviewLoader;

    abstract public function load(
        Request $request,
        ChannelContext $context,
        string $productId,
        ?string $productParentId = null
    ): ProductReviewResult;
}
