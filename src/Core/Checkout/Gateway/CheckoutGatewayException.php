<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class CheckoutGatewayException extends HttpException
{
    public const PAYLOAD_INVALID_CODE = 'CHECKOUT_GATEWAY__PAYLOAD_INVALID';

    public const HANDLER_NOT_FOUND_CODE = 'CHECKOUT_GATEWAY__HANDLER_NOT_FOUND';

    public const HANDLER_EXCEPTION = 'CHECKOUT_GATEWAY__HANDLER_EXCEPTION';

    public static function handlerNotFound(string $commandKey): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::HANDLER_NOT_FOUND_CODE,
            'Handler not found for command "{{ key }}"',
            ['key' => $commandKey]
        );
    }

    /**
     * @param array<string, string|\Stringable> $parameters
     */
    public static function handlerException(string $message, array $parameters = []): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::HANDLER_EXCEPTION,
            $message,
            $parameters
        );
    }
}
