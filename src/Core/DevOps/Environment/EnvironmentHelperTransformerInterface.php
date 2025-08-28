<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\Environment;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface EnvironmentHelperTransformerInterface
{
    public static function transform(EnvironmentHelperTransformerData $data): void;
}
