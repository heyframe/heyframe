<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Filesystem\Adapter;

use HeyFrame\Core\Framework\Log\Package;
use League\Flysystem\FilesystemAdapter;

#[Package('framework')]
interface AdapterFactoryInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function create(array $config): FilesystemAdapter;

    public function getType(): string;
}
