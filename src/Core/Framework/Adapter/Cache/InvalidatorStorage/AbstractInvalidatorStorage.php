<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\InvalidatorStorage;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractInvalidatorStorage
{
    /**
     * @param array<string> $tags
     */
    abstract public function store(array $tags): void;

    /**
     * @return list<string>
     */
    abstract public function loadAndDelete(): array;
}
