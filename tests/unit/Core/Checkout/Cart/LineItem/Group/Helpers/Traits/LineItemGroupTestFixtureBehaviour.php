<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Traits;

use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupDefinition;
use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
trait LineItemGroupTestFixtureBehaviour
{
    private function buildGroup(string $packagerKey, float $value, string $sorterKey, RuleCollection $rules): LineItemGroupDefinition
    {
        $group = new LineItemGroupDefinition(
            Uuid::randomBytes(),
            $packagerKey,
            $value,
            $sorterKey,
            $rules
        );

        return $group;
    }
}
