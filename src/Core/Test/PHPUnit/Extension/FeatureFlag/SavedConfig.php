<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\PHPUnit\Extension\FeatureFlag;

use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type FeatureFlagConfig from Feature
 */
#[Package('framework')]
class SavedConfig
{
    /**
     * @var array<string, FeatureFlagConfig>|null
     */
    public ?array $savedFeatureConfig = null;

    /**
     * @var array<string, mixed>
     */
    public array $savedServerVars = [];
}
