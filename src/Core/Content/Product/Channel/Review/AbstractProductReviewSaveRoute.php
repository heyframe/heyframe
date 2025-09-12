<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\NoContentResponse;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('after-sales')]
abstract class AbstractProductReviewSaveRoute
{
    abstract public function getDecorated(): AbstractProductReviewSaveRoute;

    abstract public function save(string $productId, RequestDataBag $data, ChannelContext $context): NoContentResponse;
}
