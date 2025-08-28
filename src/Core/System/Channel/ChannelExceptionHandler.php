<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
/**
 * @deprecated tag:v6.8.0 - reason:remove-subscriber - Will be removed, as the exception handler is no longer needed, languages now also throw RestrictDeleteViolationException
 * @see RestrictDeleteViolationException is now thrown instead
 */
class ChannelExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Throwable $e): ?\Throwable
    {
        return null;
    }
}
