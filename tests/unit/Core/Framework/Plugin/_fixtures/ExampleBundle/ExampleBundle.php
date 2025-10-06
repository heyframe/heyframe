<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Plugin\_fixtures\ExampleBundle;

use HeyFrame\Core\Framework\Parameter\AdditionalBundleParameters;
use HeyFrame\Core\Framework\Plugin;
use HeyFrame\Tests\Unit\Core\Framework\Plugin\_fixtures\ExampleBundle\FeatureA\FeatureA;
use HeyFrame\Tests\Unit\Core\Framework\Plugin\_fixtures\ExampleBundle\FeatureB\FeatureB;

/**
 * @internal
 */
class ExampleBundle extends Plugin
{
    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return [
            new FeatureA(),
            new FeatureB(),
        ];
    }
}
