<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class ProductNumberNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $number)
    {
        parent::__construct(
            'Product with number "{{ number }}" not found.',
            ['number' => $number]
        );
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__PRODUCT_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
