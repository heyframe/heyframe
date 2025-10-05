<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed, use PromotionException::discountCalculatorNotFound() instead
 */
#[Package('checkout')]
class DiscountCalculatorNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $type)
    {
        parent::__construct('Promotion Discount Calculator "{{ type }}" has not been found!', ['type' => $type]);
    }

    public function getErrorCode(): string
    {
        return 'CHECKOUT__DISCOUNT_CALCULATOR_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
