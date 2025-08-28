<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\Test\Environment\_fixtures;

use HeyFrame\Core\DevOps\Environment\EnvironmentHelperTransformerData;
use HeyFrame\Core\DevOps\Environment\EnvironmentHelperTransformerInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class EnvironmentHelperTransformer implements EnvironmentHelperTransformerInterface
{
    public static function transform(EnvironmentHelperTransformerData $data): void
    {
        $data->setValue($data->getValue() !== null ? $data->getValue() . ' bar' : null);
        $data->setDefault($data->getDefault() . ' baz');
    }
}
