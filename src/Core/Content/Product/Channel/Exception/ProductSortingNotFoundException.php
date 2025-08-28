<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class ProductSortingNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $key)
    {
        parent::__construct(
            'Product sorting with key {{ key }} not found.',
            ['key' => $key]
        );
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__PRODUCT_SORTING_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
