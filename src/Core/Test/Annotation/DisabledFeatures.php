<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Annotation;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
#[Package('framework')]
final class DisabledFeatures
{
    /**
     * @param array<string> $features
     */
    public function __construct(public array $features = [])
    {
    }
}
