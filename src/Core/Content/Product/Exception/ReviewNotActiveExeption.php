<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed. Use ProductException::reviewNotActive instead
 */
#[Package('inventory')]
class ReviewNotActiveExeption extends HeyFrameHttpException
{
    public function __construct()
    {
        parent::__construct('Reviews not activated');
    }

    public function getErrorCode(): string
    {
        return 'PRODUCT__REVIEW_NOT_ACTIVE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
