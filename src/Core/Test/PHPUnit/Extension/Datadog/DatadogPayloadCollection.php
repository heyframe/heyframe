<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\PHPUnit\Extension\Datadog;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @internal
 *
 * @extends Collection<DatadogPayload>
 */
#[Package('framework')]
class DatadogPayloadCollection extends Collection
{
}
