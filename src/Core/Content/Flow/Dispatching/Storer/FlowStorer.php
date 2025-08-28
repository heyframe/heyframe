<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
abstract class FlowStorer
{
    /**
     * @param array<string, mixed> $stored
     *
     * @return array<string, mixed>
     */
    abstract public function store(FlowEventAware $event, array $stored): array;

    abstract public function restore(StorableFlow $storable): void;
}
