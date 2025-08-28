<?php

declare(strict_types=1);

namespace HeyFrame\Core\Content\Rule;

use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\UnsupportedCommandTypeException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
class RuleException extends HttpException
{
    public static function unsupportedCommandType(WriteCommand $command): HttpException
    {
        return new UnsupportedCommandTypeException($command);
    }
}
