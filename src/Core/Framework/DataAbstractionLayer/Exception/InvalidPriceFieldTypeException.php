<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Framework\HeyFrameHttpException;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed, use CartException::invalidPriceFieldTypeException() instead
 */
class InvalidPriceFieldTypeException extends HeyFrameHttpException
{
    public function __construct(string $type)
    {
        parent::__construct(
            'The price field does not contain a valid "type" value. Received {{ type }} ',
            ['type' => $type]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_PRICE_FIELD_TYPE';
    }
}
