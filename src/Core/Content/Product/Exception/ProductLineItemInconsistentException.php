<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class ProductLineItemInconsistentException extends HeyFrameHttpException
{
    public function __construct(string $lineItemId)
    {
        $message = \sprintf(
            'To change the product of line item (%s), the following properties must also be updated: `productId`, `referencedId`, `payload.productNumber`.',
            $lineItemId
        );

        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__PRODUCT_LINE_ITEM_INCONSISTENT';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
