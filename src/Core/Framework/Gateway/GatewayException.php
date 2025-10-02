<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway;

use GuzzleHttp\Exception\RequestException;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class GatewayException extends HttpException
{
    public const HANDLER_NOT_FOUND_CODE = 'CONTEXT_GATEWAY__HANDLER_NOT_FOUND';
    public const HANDLER_EXCEPTION = 'CONTEXT_GATEWAY__HANDLER_EXCEPTION';
    public const COMMAND_VALIDATION_FAILED = 'CONTEXT_GATEWAY__COMMAND_VALIDATION_FAILED';
    public const REQUEST_FAILED = 'CONTEXT_GATEWAY__REQUEST_FAILED';
    public const CUSTOMER_MESSAGE = 'CONTEXT_GATEWAY__CUSTOMER_MESSAGE';


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

    /**
     * @param array<string, string|\Stringable> $parameters
     */
    public static function commandValidationFailed(string $message, array $parameters = []): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::COMMAND_VALIDATION_FAILED,
            $message,
            $parameters
        );
    }

    public static function requestFailed(RequestException $previous): self
    {
        return new self(
            $previous->getResponse()?->getStatusCode() ?? Response::HTTP_BAD_REQUEST,
            self::REQUEST_FAILED,
            'Request to app failed',
            [],
            $previous
        );
    }

    public static function customerMessage(string $message): self
    {
        return new self(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            self::CUSTOMER_MESSAGE,
            $message
        );
    }
}
