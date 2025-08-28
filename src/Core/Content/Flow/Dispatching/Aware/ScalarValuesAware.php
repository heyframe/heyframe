<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Aware;

use HeyFrame\Core\Framework\Event\IsFlowEventAware;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
#[IsFlowEventAware]
interface ScalarValuesAware
{
    public const STORE_VALUES = 'store_values';

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array;
}
