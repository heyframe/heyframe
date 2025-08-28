<?php declare(strict_types=1);

namespace HeyFrame\Core\System\DependencyInjection;

use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\DefinitionNotFoundException;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class DependencyInjectionException extends HttpException
{
    public const NUMBER_RANGE_REDIS_NOT_CONFIGURED = 'SYSTEM__NUMBER_RANGE_REDIS_NOT_CONFIGURED';

    public static function redisNotConfiguredForNumberRangeIncrementer(): self
    {
        return new self(
            500,
            self::NUMBER_RANGE_REDIS_NOT_CONFIGURED,
            'Parameter "heyframe.number_range.config.connection" is required for redis storage'
        );
    }

    public static function definitionNotFound(string $entity): DefinitionNotFoundException
    {
        return new DefinitionNotFoundException($entity);
    }
}
