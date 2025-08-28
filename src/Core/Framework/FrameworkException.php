<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework;

use Symfony\Component\HttpFoundation\Response;

class FrameworkException extends HttpException
{
    private const INVALID_ARGUMENT = 'FRAMEWORK__INVALID_ARGUMENT';
    private const CONTEXT_RULES_LOCKED = 'FRAMEWORK__CONTEXT_RULES_LOCKED';

    public static function invalidArgumentException(string $message): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_ARGUMENT,
            $message
        );
    }

    public static function contextRulesLocked(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CONTEXT_RULES_LOCKED,
            'Context rules in application context already locked.'
        );
    }
}
