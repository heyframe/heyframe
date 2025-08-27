<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\Environment;

interface EnvironmentHelperTransformerInterface
{
    public static function transform(EnvironmentHelperTransformerData $data): void;
}
